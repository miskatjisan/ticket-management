<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Reviewer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManageReviewerController extends Controller
{
    public function all()
    {
        $pageTitle = 'All Reviewers';
        $reviewers = Reviewer::orderBy('id', 'desc')->paginate(getPaginate());

        return view('admin.reviewers', compact('pageTitle', 'reviewers'));
    }

    public function updateStatus($id)
    {
        $reviewer = Reviewer::findOrFail($id);
        $reviewer->status = $reviewer->status ? 0 : 1;
        $reviewer->save();

        $notification = 'Reviewer banned successfully';
        if ($reviewer->status) {
            $notification = 'Reviewer unbanned Successfully';
        }

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function save(Request $request, $id = 0)
    {
        $passwordValidation = 'required';
        if ($id) {
            $passwordValidation = 'nullable';
        }
        $request->validate([
            'name' => 'required|string|max:40',
            'username' => 'required|string|max:40|unique:reviewers,username,' . $id,
            'email' => 'required|email|unique:reviewers,email,' . $id,
            'password' => $passwordValidation
        ]);

        if ($id) {
            $reviewer = Reviewer::findOrFail($id);
            $notification = 'Reviewer updated successfully';
        } else {
            $reviewer = new Reviewer();
            $notification = 'Reviewer added successfully';
        }

        $reviewer->name = $request->name;
        $reviewer->email = $request->email;
        $reviewer->username = $request->username;
        $reviewer->password = Hash::make($request->password);
        $reviewer->status = 1;
        $reviewer->save();

        if (!$id) {
            notify($reviewer, 'REVIEWER_CREATED', [
                'time' => showDateTime(now(), 'd M, Y h:i A')
            ]);
        }

        if ($id && $request->password) {
            notify($reviewer, 'REVIEWER_PASSWORD_UPDATE', [
                'time' => showDateTime(now(), 'd M, Y h:i A')
            ]);
        }

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function login($id)
    {
        $reviewer = Reviewer::where('status', 1)->findOrFail($id);
        Auth::guard('reviewer')->loginUsingId($reviewer->id);
        return to_route('reviewer.dashboard');
    }
}
