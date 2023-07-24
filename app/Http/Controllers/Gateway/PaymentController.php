<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\AdminNotification;
use App\Models\Deposit;
use App\Models\GatewayCurrency;
use App\Models\Transaction;
use App\Models\Plan;
use App\Models\PlanPurchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function deposit()
    {
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('method_code')->get();
        $pageTitle = 'Deposit Methods';
        return view($this->activeTemplate . 'user.payment.deposit', compact('gatewayCurrency', 'pageTitle'));
    }

    //for plan purchase
    public function payment(Request $request)
    {
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('method_code')->get();

        $pageTitle = 'Payment Methods';
        $plan = Plan::active()->where('id', $request->plan_id)->first();

        if (!$plan) {
            $notify[] = ['error', 'Plan not found'];
            return back()->withNotify($notify);
        }

        $period = $request->period;
        return view($this->activeTemplate . 'user.payment.deposit', compact('gatewayCurrency', 'pageTitle', 'plan', 'period'));
    }

    public function depositInsert(Request $request)
    {
        $request->validate([
            'type'        => 'required|in:payment,deposit',
            'amount'      => 'required|numeric|gt:0',
            'method_code' => 'required',
            'currency'    => 'required',
            'plan_id'     => 'required_if:type,payment|numeric|gt:0',
            'period'      => 'required_if:type,payment|in:monthly,yearly',
        ]);

        //for plan purchase
        $plan  = null;
        $amount = $request->amount;
        $limitNotification = 'Please follow deposit limit';

        if ($request->plan_id) {
            $plan = Plan::active()->where('id', $request->plan_id)->first();
            if (!$plan) {
                $notify[] = ['error', 'Plan not found'];
                return back()->withNotify($notify);
            }

            $price = $request->period . '_price';

            if ($plan->$price != $amount) {
                $notify[] = ['error', 'Amount must be equal to plan\'s ' . $request->period . ' price'];
                return back()->withNotify($notify);
            }

            $amount = $plan->$price;
            $limitNotification = 'Please follow payment limit';
        }

        $user = auth()->user();
        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', $request->method_code)->where('currency', $request->currency)->first();

        if (!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }

        if ($gate->min_amount > $amount || $gate->max_amount < $amount) {
            $notify[] = ['error', $limitNotification];
            return back()->withNotify($notify);
        }

        $charge    = $gate->fixed_charge + ($amount * $gate->percent_charge / 100);
        $payable   = $amount + $charge;
        $final_amo = $payable * $gate->rate;

        $deposit                  = new Deposit();
        $deposit->user_id         = $user->id;
        $deposit->plan_id         = $request->plan_id ?? 0;
        $deposit->period          = $request->period ?? null;
        $deposit->method_code     = $gate->method_code;
        $deposit->method_currency = strtoupper($gate->currency);
        $deposit->amount          = $amount;
        $deposit->charge          = $charge;
        $deposit->rate            = $gate->rate;
        $deposit->final_amo       = $final_amo;
        $deposit->btc_amo         = 0;
        $deposit->btc_wallet      = "";
        $deposit->trx             = getTrx();
        $deposit->save();
        session()->put('Track', $deposit->trx);
        return to_route('user.deposit.confirm');
    }


    public function appDepositConfirm($hash)
    {
        try {
            $id = decrypt($hash);
        } catch (\Exception $ex) {
            return "Sorry, invalid URL.";
        }
        $data = Deposit::where('id', $id)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->firstOrFail();
        $user = User::findOrFail($data->user_id);
        auth()->login($user);
        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }


    public function depositConfirm()
    {
        $track = session()->get('Track');
        $deposit = Deposit::where('trx', $track)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();

        if ($deposit->method_code >= 1000) {
            return to_route('user.deposit.manual.confirm');
        }


        $dirName = $deposit->gateway->alias;
        $new = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);


        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return to_route(gatewayRedirectUrl())->withNotify($notify);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if (@$data->session) {
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $pageTitle = 'Payment Confirm';
        return view($this->activeTemplate . $data->view, compact('data', 'pageTitle', 'deposit'));
    }


    public static function userDataUpdate($deposit, $isManual = null)
    {
        if ($deposit->status == Status::PAYMENT_INITIATE || $deposit->status == Status::PAYMENT_PENDING) {
            $user    = User::find($deposit->user_id);
            $general = gs();

            //deposit status update
            $deposit->status = Status::PAYMENT_SUCCESS;
            $deposit->save();

            $user->balance += $deposit->amount;
            $user->save();

            //transaction log
            $transaction               = new Transaction();
            $transaction->user_id      = $deposit->user_id;
            $transaction->amount       = $deposit->amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge       = $deposit->charge;
            $transaction->trx_type     = '+';
            $transaction->details      = 'Deposit via ' . $deposit->gatewayCurrency()->name;
            $transaction->trx          = $deposit->trx;
            $transaction->remark       = 'deposit';
            $transaction->save();

            if ($deposit->plan_id) {
                $plan  = Plan::active()->where('id', $deposit->plan_id)->first();
                if (!$plan) {
                    $notify[] = ['error', 'Plan not found'];
                    return to_route('plans')->withNotify($notify);
                }

                $user->balance -= $deposit->amount;
                $user->save();

                //transaction log
                $transaction               = new Transaction();
                $transaction->user_id      = $deposit->user_id;
                $transaction->amount       = $deposit->amount;
                $transaction->post_balance = $user->balance;
                $transaction->charge       = 0;
                $transaction->trx_type     = '-';
                $transaction->details      = 'Payment via ' . $deposit->gatewayCurrency()->name;
                $transaction->trx          = $deposit->trx;
                $transaction->remark       = 'payment';
                $transaction->save();

                // referral commission
                if ($general->referral_system && $user->ref_by) {
                    referCommission($user, $deposit->amount, $deposit->trx);
                }

                $planPurchase = PlanPurchase::where('user_id', $user->id)->first();
                if (!$planPurchase) {
                    $planPurchase = new PlanPurchase();
                }

                //purchase plan
                $planPurchase->user_id         = $user->id;
                $planPurchase->plan_id         = $plan->id;
                $planPurchase->daily_limit     = $plan->daily_limit;
                $planPurchase->monthly_limit   = $plan->monthly_limit;
                $planPurchase->trx             = $deposit->trx;
                $planPurchase->amount          = $deposit->amount;
                $planPurchase->purchase_date   = Carbon::now();
                if ($deposit->period == 'monthly') {
                    $planPurchase->expired_at = Carbon::now()->addMonth();
                } else {
                    $planPurchase->expired_at = Carbon::now()->addYear();
                }
                $planPurchase->save();
            }

            if (!$isManual) {
                $adminNotification            = new AdminNotification();
                $adminNotification->user_id   = $user->id;
                $adminNotification->title     = 'Payment successful via ' . $deposit->gatewayCurrency()->name;
                $adminNotification->click_url = urlPath('admin.deposit.successful');
                $adminNotification->save();
            }

            //send notification
            notify($user, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                'method_name'     => $deposit->gatewayCurrency()->name,
                'method_currency' => $deposit->method_currency,
                'method_amount'   => showAmount($deposit->final_amo),
                'amount'          => showAmount($deposit->amount),
                'charge'          => showAmount($deposit->charge),
                'rate'            => showAmount($deposit->rate),
                'trx'             => $deposit->trx,
                'post_balance'    => showAmount($user->balance)
            ]);

            if (isset($plan)) {
                notify($user, $isManual ? 'PURCHASE_REQUEST_APPROVE' : 'PLAN_PURCHASED', [
                    'plan_name'    => $plan->name,
                    'amount'       => showAmount($transaction->amount),
                    'trx'          => $transaction->trx,
                    'charge'       => showAmount($transaction->charge),
                    'method_name'  => $deposit->gatewayCurrency()->name,
                    'post_balance' => showAmount($transaction->post_balance),
                    'expired_at'   => showDateTime($planPurchase->expired_at, 'F j, Y')
                ]);
            }
        }
    }

    public function manualDepositConfirm()
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        if (!$data) {
            return to_route(gatewayRedirectUrl());
        }
        if ($data->method_code > 999) {

            $pageTitle = ($data->plan_id ? 'Payment' : 'Deposit') . ' Confirm';
            $method = $data->gatewayCurrency();
            $gateway = $method->method;
            return view($this->activeTemplate . 'user.payment.manual', compact('data', 'pageTitle', 'method', 'gateway'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request)
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        if (!$data) {
            return to_route(gatewayRedirectUrl());
        }
        $gatewayCurrency = $data->gatewayCurrency();
        $gateway = $gatewayCurrency->method;
        $formData = $gateway->form->form_data;

        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);


        $data->detail = $userData;
        $data->status = Status::PAYMENT_PENDING;
        $data->save();

        $url        = urlPath('admin.deposit.details', $data->id);
        $message    = 'Your deposit request has been taken';
        $notifyType = 'DEPOSIT_REQUEST';

        $notifyBody = [
            'method_name'     => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount'   => showAmount($data->final_amo),
            'amount'          => showAmount($data->amount),
            'charge'          => showAmount($data->charge),
            'rate'            => showAmount($data->rate),
            'trx'             => $data->trx
        ];


        if ($data->plan_id) {
            $plan       = Plan::active()->where('id', $data->plan_id)->first();
            if (!$plan) {
                $notify[] = ['error', 'Plan not found'];
                return to_route('plans')->withNotify($notify);
            }

            $url        = urlPath('admin.payment.details', $data->id);
            $message    = 'Your payment request has been taken';
            $notifyType = 'PAYMENT_REQUEST';

            $notifyBody = [
                'plan_name' => $plan->name,
                'method_name' => $data->gatewayCurrency()->name,
                'method_currency' => $data->method_currency,
                'method_amount' => showAmount($data->final_amo),
                'amount' => showAmount($data->amount),
                'charge' => showAmount($data->charge),
                'rate' => showAmount($data->rate),
                'trx' => $data->trx
            ];
        }

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $data->user->id;
        $adminNotification->title     = ($data->plan_id ? 'Payment' : 'Deposit') . ' request from ' . $data->user->username;
        $adminNotification->click_url = $url;
        $adminNotification->save();

        notify($data->user, $notifyType, $notifyBody);

        $notify[] = ['success', $message];
        return to_route('user.deposit.history')->withNotify($notify);
    }
}
