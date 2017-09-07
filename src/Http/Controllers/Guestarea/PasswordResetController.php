<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Controllers\Guestarea;

use Illuminate\Support\Str;
use Rinvex\Fort\Contracts\PasswordResetBrokerContract;
use Cortex\Foundation\Http\Controllers\AbstractController;
use Cortex\Fort\Http\Requests\Guestarea\PasswordResetRequest;
use Cortex\Fort\Http\Requests\Guestarea\PasswordResetSendRequest;
use Cortex\Fort\Http\Requests\Guestarea\PasswordResetProcessRequest;
use Cortex\Fort\Http\Requests\Guestarea\PasswordResetPostProcessRequest;

class PasswordResetController extends AbstractController
{
    /**
     * Show the password reset request form.
     *
     * @param Cortex\Fort\Http\Requests\Guestarea\PasswordResetRequest
     *
     * @return \Illuminate\Http\Response
     */
    public function request(PasswordResetRequest $request)
    {
        return view('cortex/fort::guestarea.forms.passwordreset-request');
    }

    /**
     * Process the password reset request form.
     *
     * @param \Cortex\Fort\Http\Requests\Guestarea\PasswordResetSendRequest $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function send(PasswordResetSendRequest $request)
    {
        $result = app('auth.password')
            ->broker($this->getBroker())
            ->sendResetLink($request->only(['email']));

        switch ($result) {
            case PasswordResetBrokerContract::RESET_LINK_SENT:
                return intend([
                    'url' => route('guestarea.home'),
                    'with' => ['success' => trans($result)],
                ]);

            case PasswordResetBrokerContract::INVALID_USER:
            default:
                return intend([
                    'back' => true,
                    'withInput' => $request->only(['email']),
                    'withErrors' => ['email' => trans($result)],
                ]);
        }
    }

    /**
     * Show the password reset form.
     *
     * @param \Cortex\Fort\Http\Requests\Guestarea\PasswordResetProcessRequest $request
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function reset(PasswordResetProcessRequest $request)
    {
        $credentials = $request->only('email', 'expiration', 'token');

        return view('cortex/fort::guestarea.forms.passwordreset')->with($credentials);
    }

    /**
     * Process the password reset form.
     *
     * @param \Cortex\Fort\Http\Requests\Guestarea\PasswordResetPostProcessRequest $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function process(PasswordResetPostProcessRequest $request)
    {
        $result = app('auth.password')
            ->broker($this->getBroker())
            ->reset($request->only(['email', 'expiration', 'token', 'password', 'password_confirmation']), function ($user, $password) {
                $user->fill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->forceSave();
            });

        switch ($result) {
            case PasswordResetBrokerContract::PASSWORD_RESET:
                return intend([
                    'url' => route('guestarea.auth.login'),
                    'with' => ['success' => trans($result)],
                ]);

            case PasswordResetBrokerContract::INVALID_USER:
            case PasswordResetBrokerContract::INVALID_TOKEN:
            case PasswordResetBrokerContract::EXPIRED_TOKEN:
            case PasswordResetBrokerContract::INVALID_PASSWORD:
            default:
                return intend([
                    'back' => true,
                    'withInput' => $request->only(['email']),
                    'withErrors' => ['email' => trans($result)],
                ]);
        }
    }
}
