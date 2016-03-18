<?php

namespace App\Http\Controllers\Auth;

use App\Avistaz\Services\EmailDomainVerifier;
use App\Http\Controllers\Controller;
use App\Jobs\SendForgotPasswordMail;
use App\Jobs\SendRegistrationMail;
use App\Models\User;
use Auth;
use Cookie;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use ReCaptcha\ReCaptcha;
use Validator;

class AuthController extends Controller
{
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    protected $group_id = '9';

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * User can login with either Email or Username.
     *
     * @var string
     */
    protected $username = 'email_username';

    /**
     * Create a new authentication controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $auth = Auth::guard($this->getGuard());

        $this->validateLogin($request);

        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $lockedOut = $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->getCredentials($request);

        if ($auth->attempt($credentials, $request->has('remember'))) {
            if ($auth->user()->confirmed != 1) {
                flash()->warning('This account is not confirmed, Please check your email for confirmation link. If you did not receive the confirmation code, please request for new confirmation code.');
                $auth->logout();

                return redirect('auth/login')->with('show_reconfirm', true);
            }

            return $this->handleUserWasAuthenticated($request, $throttles);
        }

        if ($throttles && !$lockedOut) {
            $this->incrementLoginAttempts($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        // Check for duplicate user
        if ($this->checkForDupeUser()) {
            flash()->error('Your attempt to create duplicate account has been logged. If you believe this is an error please use IRC Chat!');

            return redirect()->back()->withInput($request->all());
        }

        // validate Email Address
        $email_verify = $this->verifyEmail($request->get('email'));
        if ($email_verify != 'OK') {
            return redirect()->back()->withInput($request->all())->withErrors([
                'email' => 'Rejected! ' . $email_verify . '. Please use different email address.',
            ]);
        }

        // Verify Recaptcha
        $recaptcha = new ReCaptcha(env('API_GOOGLE_RECAPTCHA'));
        $resp = $recaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp());
        if (!$resp->isSuccess()) {
            flash()->error('ReCaptcha verification failed!');

            return redirect()->back()->withInput($request->all());
        }

        $user = $this->create($request->all());
        if (!empty($user->id)) {
            return $this->sendRegistrationEmail($user);
        }

        flash()->error('Failed to Register! Please try again.');

        return redirect()->back();
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function getCredentials(Request $request)
    {
        $is_email = filter_var($request->get($this->loginUsername()), FILTER_VALIDATE_EMAIL);

        return [
            $is_email ? 'email' : 'username' => $request->get($this->loginUsername()),
            'password'                       => $request->get('password'),
        ];
    }

    public function getForgot()
    {
        $this->meta->setMeta('Forgot Password');

        return view('auth.forgot', $this->data);
    }

    public function postForgot(Request $request)
    {
        $this->validate($request, [
            'email_username' => 'required',
        ]);

        if (filter_var($request->get($this->loginUsername()), FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->get($this->loginUsername()))->first();
        } else {
            $user = User::where('username', $request->get($this->loginUsername()))->first();
        }

        if ($user) {
            if ($user->forgot_at > carbon()->subMinutes(11)) {
                flash()->error('Forgot password already requested. Please wait for at least 10 minutes before requesting for new password reset!');

                return redirect('auth/login');
            }

            $user->forgot = 1;
            $user->forgot_at = carbon();
            $user->forgot_token = md5($user->username . microtime() . $user->email . env('APP_KEY'));
            $user->save();

            $this->dispatch(new SendForgotPasswordMail($user));

            flash()->success("We've sent an email containing a temporary link that will allow you to reset your password for the next 24 hours. Please check your spam folder if the email doesn't appear within a few minutes.");

            return redirect('auth/login');
        }

        flash()->error('Invalid Username or Email Address, no user found');

        return redirect('auth/forgot');
    }

    public function confirm($user_id, $token)
    {
        $user = User::where('id', '=', (int)$user_id)
            ->where('confirm_token', '=', $token)
            ->first();
        if ($user) {
            $user->confirmed = 1;
            $user->confirm_token = null;
            $user->save();

            flash()->success('Account confirmed successfully. You may login now.');

            return redirect('auth/login');
        }

        flash()->error('Invalid confirmation code or Account already confirmed!');

        return redirect('auth/login')->with('show_reconfirm', true);
    }

    public function getReconfirm()
    {
        $this->meta->setMeta('Request New Confirmation Code');

        return view('auth.reconfirm', $this->data);
    }

    public function postReconfirm(Request $request)
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];
        if (!$request->has('old_email')) {
            $rules['email'] = 'required|email|unique:users';
        }
        $this->validate($request, $rules);

        $credentials['username'] = $request->get('username');
        $credentials['password'] = $request->get('password');

        if (auth()->validate($credentials)) {
            $user = User::where('username', '=', $credentials['username'])->firstOrFail();
            if ($user->confirmed == 1) {
                flash()->error('User already confirmed, Please login');

                return redirect('auth/login');
            }

            if ($user->updated_at > carbon()->subMinutes(6)) {
                flash()->error('Account Confirmation already requested. Please wait for at least 5 minutes before requesting for new confirmation code!');

                return redirect()->back()->withInput($request->all());
            }

            if (!$request->has('old_email')) {
                $user->email = $request->get('email');
            }
            $user->confirm_token = $this->createActiveToken($user->toArray());
            $user->confirmed = 0;
            $user->save();

            return $this->sendRegistrationEmail($user);
        }

        flash()->error('Invalid Username or Password');

        return redirect()->back()->withInput($request->all());
    }

    public function getReset($user_id, $token)
    {
        $this->meta->setMeta('Reset Password');
        $this->data['id'] = $user_id;
        $this->data['forgot_token'] = $token;

        $user = User::where('id', (int)$user_id)->where('forgot_token', $token)->where('forgot', 1)->first();
        if ($user) {
            $this->data['user'] = $user;

            return view('auth.reset', $this->data);
        }

        flash()->error('Invalid password reset code!');

        return redirect('auth/login');
    }

    public function postReset(Request $request, $id, $forgot_token)
    {
        $this->validate($request, [
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::where('id', (int)$id)->where('forgot_token', $forgot_token)->where('forgot', 1)->first();
        if ($user) {
            $user->password = bcrypt($request->get('password'));
            if ($user->confirmed != 1) {
                $user->confirmed = 1;
                $user->confirm_token = null;
            }

            $user->forgot = null;
            $user->forgot_token = null;
            $user->save();

            flash()->success('Your password has been successfully reset. Please login with your new password.');

            return redirect('auth/login');
        }

        flash()->error('Invalid password reset code!');

        return redirect('auth/login');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username'             => 'required|alpha_dash|min:3|max:20|Unique:users',
            'email'                => 'required|email|max:255|unique:users',
            'password'             => 'required|confirmed|min:6',
            'g-recaptcha-response' => 'required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'group_id'      => $this->group_id,
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password'      => bcrypt($data['password']),
            'pid'           => md5($data['username'] . $data['email'] . microtime() . $data['password'] . env('APP_KEY')),
            'ip_address'    => get_ip(),
            'country_code'  => geoip_country_code(get_ip()),
            'show_adult'    => true,
            'can_download'  => true,
            'can_upload'    => true,
            'confirm_token' => $this->createActiveToken($data),
            'confirmed'     => false,
        ]);
    }

    /**
     * Create an unique Email activation/confirmation token.
     *
     * @param array $data
     *
     * @return string
     */
    protected function createActiveToken(array $data)
    {
        return md5($data['username'] . $data['email'] . microtime() . env('APP_KEY'));
    }

    private function checkForDupeUser()
    {
        if (config('app.debug') && app()->environment() == 'local') {
            return false;
        }

        if (Cookie::has(env('APP_PREFIX') . 'love')) {
            return true;
        }

        $dupe_ips = User::where('ip_address', get_ip())->count();
        if ($dupe_ips > 1 && $dupe_ips < 11) {
            return true;
        }

        $dupe_ips = Tracker::where('ip', get_ip())->groupBy('created_by')->count();
        if ($dupe_ips > 1 && $dupe_ips < 11) {
            return true;
        }

        return false;
    }

    private function verifyEmail($email)
    {
        return app(EmailDomainVerifier::class)->verify($email);
    }

    private function sendRegistrationEmail($user)
    {
        $this->dispatch(new SendRegistrationMail($user));

        flash()->info('You have been successfully registered. A confirmation email has been sent to "' . e($user->email) . '" Please confirm your email address, before you login.');

        return redirect('auth/login')->with('show_reconfirm', true);
    }
}
