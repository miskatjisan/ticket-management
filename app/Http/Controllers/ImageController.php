<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Lib\DownloadFile;
use Illuminate\Http\Request;
use App\Models\Download;
use App\Models\EarningLog;
use App\Models\Image;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ImageController extends Controller
{

    public function download($id)
    {
        $image = Image::findOrFail(decrypt($id));
        $user    = auth()->user();

        // for premium
        if (!$image->is_free && $user->id != $image->user_id) {
            $this->premiumDownloadProcess($image);
        }
        $this->downloadData($image, $user);
        session()->flash('is_download', 'downloaded');
        return DownloadFile::download($image);
    }

    //save download data
    protected function downloadData($image, $user)
    {
        $general = gs();

        if ($image->user->id != @$user->id) {
            if ($user) {
                $download = Download::where('image_id', $image->id)->where('user_id', $user->id)->first();
                if (!$download) {
                    $download = new Download();
                    $download->user_id = $user->id;
                    $image->total_downloads += 1;
                }
            } else {
                $download = new Download();
                $image->total_downloads += 1;
            }

            $isDownloaded = Download::where('image_id', $image->id)->where('user_id', @$user->id)->exists();

            $download->image_id = $image->id;
            $download->contributor_id =  $image->user->id;
            $download->ip = request()->ip();
            $download->premium = $image->is_free ? Status::DISABLE : Status::ENABLE;


            if (!$image->is_free && !$isDownloaded) {
                $amount = $image->price * $general->per_download / 100;
                $image->user->balance +=  $amount;
                $image->user->update();

                $earn                 = new EarningLog();
                $earn->contributor_id = $image->user->id;
                $earn->image_id       = $image->id;
                $earn->amount         = $amount;
                $earn->date           = Carbon::now()->format('Y-m-d');
                $earn->save();

                $transaction               = new Transaction();
                $transaction->user_id      = $image->user->id;
                $transaction->amount       =  $amount;
                $transaction->post_balance = getAmount($image->user->balance);
                $transaction->charge       = 0;
                $transaction->trx_type     = '+';
                $transaction->details      = "Earnings from download '$image->title'";
                $transaction->trx          = getTrx();
                $transaction->remark       = 'earning_log';
                $transaction->save();
            }

            $image->save();
            $download->save();
        }
    }


    private function premiumDownloadProcess($image)
    {
        $user = auth()->user();
        if (!$user) {
            throw ValidationException::withMessages(['user' => 'You can not download premium photo without any account']);
        }

        $alreadyDownload = Download::where('image_id', $image->id)->where('user_id', $user->id)->exists();
        if ($alreadyDownload) {
            return 0;
        }

        if ($user->purchasedPlan && $user->purchasedPlan->expired_at > Carbon::now()->format('Y-m-d')) {
            $this->purchaseProcessByPlan($image, $user);
        } else {
            $this->purchaseProcessByBalance($image, $user);
        }
    }

    private function purchaseProcessByPlan($image, $user)
    {
        $downloads       = Download::where('image_id', $image->id)->where('user_id', $user->id)->where('premium', Status::YES);
        $todayDownload   = (clone $downloads)->whereDate('created_at', Carbon::now())->count();
        $monthlyDownload = (clone $downloads)->whereMonth('created_at', Carbon::now()->month)->count();
        if ($user->purchasedPlan->daily_limit <= $todayDownload) {
            $this->purchaseProcessByBalance($image, $user);
        }

        if ($user->purchasedPlan->monthly_limit <= $monthlyDownload) {
            $this->purchaseProcessByBalance($image, $user);
        }
    }

    private function purchaseProcessByBalance($image, $user)
    {
        if ($user->balance < $image->price) {
            throw ValidationException::withMessages(['limit_over' => 'You don\'t have sufficient balance']);
        }

        $user->balance -= $image->price;
        $user->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $image->price;
        $transaction->post_balance = getAmount($user->balance);
        $transaction->charge       = 0;
        $transaction->trx_type     = '-';
        $transaction->details      = "Charge for download - '$image->title'";
        $transaction->trx          = getTrx();
        $transaction->remark       = 'download_charge';
        $transaction->save();

        notify($user, 'PURCHASE_CHARGE', [
            'image_title'   => $image->title,
            'charge_amount' => showAmount($transaction->amount),
            'post_balance'  => showAmount($transaction->post_balance),
            'trx'           => $transaction->trx
        ]);
    }
}
