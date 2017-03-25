<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserActivation;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{

    use ResetsPasswords;

    protected $redirectTo = '/my';

    public function __construct()
    {
        $this->middleware('guest');

        parent::__construct();

        meta()->setMeta('Reset Password');
    }

    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password'       => bcrypt($password),
            'remember_token' => Str::random(60),
            'active'         => true,
        ])->save();

        UserActivation::where('user_id', $user->id)->delete();

        $this->guard()->login($user);
    }

    protected function rules()
    {
        return [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:6',
        ];
    }

    protected function validationErrorMessages()
    {
        return [
            'password.password_policy' => 'Choose a stronger password, at least one uppercase letter with number or symbol.',
        ];
    }
}
