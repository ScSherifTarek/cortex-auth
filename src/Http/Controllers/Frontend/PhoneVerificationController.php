<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Controllers\Frontend;

use Carbon\Carbon;
use Rinvex\Fort\Guards\SessionGuard;
use Cortex\Foundation\Http\Controllers\AbstractController;
use Cortex\Fort\Http\Requests\Frontend\PhoneVerificationRequest;
use Cortex\Fort\Http\Requests\Frontend\PhoneVerificationSendRequest;
use Cortex\Fort\Http\Requests\Frontend\PhoneVerificationProcessRequest;
use Cortex\Fort\Http\Requests\Frontend\PhoneVerificationSendProcessRequest;

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
        return view('cortex/fort::frontend.verification.phone-request');
    }

    /**
     * Process the phone verification request form.
     *
     * @param \Cortex\Fort\Http\Requests\Frontend\PhoneVerificationSendProcessRequest $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function send(PhoneVerificationSendProcessRequest $request)
    {
        // Send phone verification notification
        $user = $request->user($this->getGuard()) ?? auth()->guard($this->getGuard())->attemptUser();
        $user->sendPhoneVerificationNotification($request->get('method'), true);

        return intend([
            'url' => route('frontend.verification.phone.verify'),
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
        $phoneEnabled = session('_twofactor.phone');

        return view('cortex/fort::frontend.verification.phone-token', compact('phoneEnabled'));
    }

    /**
     * Process the phone verification form.
     *
     * @param \Cortex\Fort\Http\Requests\Frontend\PhoneVerificationProcessRequest $request
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function process(PhoneVerificationProcessRequest $request)
    {
        $user = $request->user($this->getGuard()) ?? auth()->guard($this->getGuard())->attemptUser();
        $result = auth()->guard($this->getGuard())->attemptTwoFactor($user, $request->get('token'));

        switch ($result) {
            case SessionGuard::AUTH_PHONE_VERIFIED:
                // Update user account
                $user->fill([
                    'phone_verified' => true,
                    'phone_verified_at' => new Carbon(),
                ])->forceSave();

                return intend([
                    'url' => route('userarea.account.settings'),
                    'with' => ['success' => trans('cortex/fort::'.$result)],
                ]);

            case SessionGuard::AUTH_LOGIN:
                auth()->guard($this->getGuard())->login($user, session('_twofactor.remember'));

                return intend([
                    'url' => route('userarea.account.settings'),
                    'with' => ['success' => trans('cortex/fort::'.$result)],
                ]);

            case SessionGuard::AUTH_TWOFACTOR_FAILED:
            default:
                return intend([
                    'back' => true,
                    'withErrors' => ['token' => trans('cortex/fort::'.$result)],
                ]);
        }
    }
}
