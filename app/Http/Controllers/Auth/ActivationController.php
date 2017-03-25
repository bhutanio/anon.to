<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserActivation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActivationController extends Controller
{
    public function activate($token)
    {
        try {
            $activation = UserActivation::with('user')->where('token', $token)->firstOrFail();
            if (!empty($activation->user->id)) {
                $activation->user->active = true;
                $activation->user->save();
                $activation->delete();
            }

            flash('Email confirmed successfully. You may login now.', 'success');
        } catch (ModelNotFoundException $e) {
            flash('Invalid confirmation code or Account already confirmed!', 'error');
        }

        return redirect('login');
    }
}
