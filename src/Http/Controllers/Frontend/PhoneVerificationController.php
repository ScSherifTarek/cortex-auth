<?php

/*
 * NOTICE OF LICENSE
 *
 * Part of the Cortex Fort Module.
 *
 * This source file is subject to The MIT License (MIT)
 * that is bundled with this package in the LICENSE file.
 *
 * Package: Cortex Fort Module
 * License: The MIT License (MIT)
 * Link:    https://rinvex.com
 */

declare(strict_types=1);

namespace Cortex\Fort\Http\Controllers\Frontend;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rinvex\Fort\Guards\SessionGuard;
use Cortex\Foundation\Http\Controllers\AbstractController;
use Cortex\Fort\Http\Requests\Frontend\PhoneVerificationRequest;
use Cortex\Fort\Http\Requests\Frontend\PhoneVerificationSendRequest;

class PhoneVerificationController extends AbstractController
{
    /**
     * Show the phone verification form.
     *
     * @param \Cortex\Fort\Http\Requests\Frontend\PhoneVerificationSendRequest $request
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function request(PhoneVerificationSendRequest $request)
    {
        if ($type = $this->invalidity($request)) {
            return $this->redirect($type);
        }

        // If Two-Factor authentication failed, remember Two-Factor persistence
        Auth::guard($this->getGuard())->rememberTwoFactor();

        return view('cortex/fort::frontend.verification.phone-request');
    }

    /**
     * Process the phone verification request form.
     *
     * @param \Cortex\Fort\Http\Requests\Frontend\PhoneVerificationSendRequest $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function send(PhoneVerificationSendRequest $request)
    {
        if ($type = $this->invalidity($request)) {
            return $this->redirect($type);
        }

        // Send phone verification notification
        $user = $request->user($this->getGuard()) ?? Auth::guard($this->getGuard())->attemptUser();
        $user->sendPhoneVerificationNotification($request->get('method'), true);

        // If Two-Factor authentication failed, remember Two-Factor persistence
        Auth::guard($this->getGuard())->rememberTwoFactor();

        return intend([
            'route' => 'frontend.verification.phone.verify',
            'with' => ['success' => trans('cortex/fort::messages.verification.phone.sent')],
        ]);
    }

    /**
     * Show the phone verification form.
     *
     * @param \Cortex\Fort\Http\Requests\Frontend\PhoneVerificationRequest $request
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function verify(PhoneVerificationRequest $request)
    {
        if ($type = $this->invalidity($request)) {
            return $this->redirect($type);
        }

        // If Two-Factor authentication failed, remember Two-Factor persistence
        Auth::guard($this->getGuard())->rememberTwoFactor();

        $methods = session('rinvex.fort.twofactor.methods');

        return view('cortex/fort::frontend.verification.phone-token', compact('methods'));
    }

    /**
     * Process the phone verification form.
     *
     * @param \Cortex\Fort\Http\Requests\Frontend\PhoneVerificationRequest $request
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function process(PhoneVerificationRequest $request)
    {
        if ($type = $this->invalidity($request)) {
            return $this->redirect($type);
        }

        $guard = $this->getGuard();
        $token = $request->get('token');
        $user = session('rinvex.fort.twofactor.user') ?: $request->user($guard);
        $result = Auth::guard($guard)->attemptTwoFactor($user, $token);

        switch ($result) {
            case SessionGuard::AUTH_PHONE_VERIFIED:
                // Update user account
                $user->fill([
                    'phone_verified' => true,
                    'phone_verified_at' => new Carbon(),
                ])->forceSave();

                return intend([
                    'route' => 'frontend.account.settings',
                    'with' => ['success' => trans($result)],
                ]);

            case SessionGuard::AUTH_LOGIN:
                Auth::guard($guard)->login($user, session('rinvex.fort.twofactor.remember'), session('rinvex.fort.twofactor.persistence'));

                return intend([
                    'route' => 'frontend.account.settings',
                    'with' => ['success' => trans($result)],
                ]);

            case SessionGuard::AUTH_TWOFACTOR_FAILED:
            default:
                // If Two-Factor authentication failed, remember Two-Factor persistence
                Auth::guard($guard)->rememberTwoFactor();

                return intend([
                    'back' => true,
                    'withErrors' => ['token' => trans($result)],
                ]);
        }
    }

    /**
     * Check request validity.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string|null
     */
    protected function invalidity(Request $request)
    {
        $user = $request->user($this->getGuard());
        $attemptUser = Auth::guard($this->getGuard())->attemptUser();
        $providers = config('rinvex.fort.twofactor.providers');

        // Not logged in, No login attempt, Phone verification attempt
        if (! $user && ! $attemptUser) {
            return 'session';
        }

        // Logged in user, No login attempt, no country, Phone verification attempt (account update)
        if ((! $attemptUser && $user && ! $user->country)) {
            return ! in_array('phone', $providers) ? 'disabled' : 'country';
        }

        // Logged in user, No login attempt, country exists, no phone, Phone verification attempt (account update)
        if ((! $attemptUser && $user && $user->country && ! $user->phone)) {
            return 'phone';
        }

        // Not logged in, Login attempt, no country, Two-Factor TOTP disabled
        if (! $user && $attemptUser && ! $attemptUser->country && ! isset(session('rinvex.fort.twofactor.methods')['totp'])) {
            return 'home';
        }
    }

    /**
     * Return redirect response.
     *
     * @param string $type
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function redirect($type)
    {
        switch ($type) {
            // Logged in user, no country, phone verification attempt (account update)
            case 'country':
                return intend([
                    'route' => 'frontend.account.settings',
                    'withErrors' => ['country' => trans('cortex/fort::messages.account.country_required')],
                ]);
                break;

            // Logged in user, Two-Factor Phone disabled, phone verification attempt (account update)
            case 'disabled':
                return intend([
                    'route' => 'frontend.account.settings',
                    'withErrors' => ['rinvex.fort.auth.twofactor.phone.disabled' => trans('cortex/fort::messages.verification.twofactor.phone.disabled')],
                ]);
                break;

            // Logged in user, No login attempt, country exists, no phone, Phone verification attempt (account update)
            case 'phone':
                return intend([
                    'route' => 'frontend.account.settings',
                    'withErrors' => ['phone' => trans('cortex/fort::messages.account.phone_required')],
                ]);
                break;

            // No login attempt, no user instance, phone verification attempt
            case 'session':
                return intend([
                    'route' => 'frontend.auth.login',
                    'withErrors' => ['rinvex.fort.auth.required' => trans('cortex/fort::messages.auth.session.required')],
                ]);
                break;

            // Login attempt, no country, enabled Two-Factor
            case 'home':
            default:
                return intend([
                    'url' => '/',
                    'withErrors' => ['rinvex.fort.auth.country' => trans('cortex/fort::messages.verification.twofactor.phone.country_required')],
                ]);
                break;
        }
    }
}
