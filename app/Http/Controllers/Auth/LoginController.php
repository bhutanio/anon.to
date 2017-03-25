<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/my';

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
        parent::__construct();

        meta()->setMeta('Login');
    }

    public function username()
    {
        return 'username';
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->active) {
            flash('This account is not activated, Please check your email for activation link. If you did not receive the activation code, please click "forgot password" link on the login page.',
                'warning');

            $this->guard()->logout();

            return redirect('login');
        }
    }
}
