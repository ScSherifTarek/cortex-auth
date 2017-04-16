<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rinvex\Fort\Guards\SessionGuard;
use Cortex\Foundation\Http\Controllers\AbstractController;
use Cortex\Fort\Http\Requests\Frontend\UserAuthenticationRequest;

class AuthenticationController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected $middlewareWhitelist = ['logout'];

    /**
     * Create a new authentication controller instance.
     */
    public function __construct()
    {
        $this->middleware($this->getGuestMiddleware(), ['except' => $this->middlewareWhitelist]);
    }

    /**
     * Show the login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function form()
    {
        // Remember previous URL for later redirect back
        session()->put('url.intended', url()->previous());

        return view('cortex/fort::frontend.authentication.login');
    }

    /**
     * Process to the login form.
     *
     * @param \Cortex\Fort\Http\Requests\Frontend\UserAuthenticationRequest $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function login(UserAuthenticationRequest $request)
    {
        // Prepare variables
        $remember = $request->has('remember');
        $loginField = get_login_field($request->get('loginfield'));
        $credentials = [
            $loginField => $request->input('loginfield'),
            'password' => $request->input('password'),
        ];

        $result = Auth::guard($this->getGuard())->attempt($credentials, $remember);

        return $this->getLoginResponse($request, $result);
    }

    /**
     * Logout currently logged in user.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        $result = Auth::guard($this->getGuard())->logout();

        return intend([
            'url' => '/',
            'with' => ['warning' => trans($result)],
        ]);
    }

    /**
     * Get login response upon the given request & result.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $result
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function getLoginResponse(Request $request, $result)
    {
        switch ($result) {
            // Too many failed logins, user locked out
            case SessionGuard::AUTH_LOCKED_OUT:
                $seconds = Auth::guard($this->getGuard())->secondsRemainingOnLockout($request);

                return intend([
                    'url' => '/',
                    'withInput' => $request->only(['loginfield', 'remember']),
                    'withErrors' => ['loginfield' => trans($result, ['seconds' => $seconds])],
                ]);

            // Valid credentials, but user is unverified; Can NOT login!
            case SessionGuard::AUTH_UNVERIFIED:
                return intend([
                    'url' => route('frontend.verification.email.request'),
                    'withErrors' => ['email' => trans($result)],
                ]);

            // Wrong credentials, failed login
            case SessionGuard::AUTH_FAILED:
                return intend([
                    'back' => true,
                    'withInput' => $request->only(['loginfield', 'remember']),
                    'withErrors' => ['loginfield' => trans($result)],
                ]);

            // Two-Factor authentication required
            case SessionGuard::AUTH_TWOFACTOR_REQUIRED:
                $route = ! isset(session('rinvex.fort.twofactor.methods')['totp']) ? route('frontend.verification.phone.request') : route('frontend.verification.phone.verify');

                return intend([
                    'url' => $route,
                    'with' => ['warning' => trans($result)],
                ]);

            // Login successful and everything is fine!
            case SessionGuard::AUTH_LOGIN:
            default:
                return intend([
                    'intended' => url('/'),
                    'with' => ['success' => trans($result)],
                ]);
        }
    }
}
