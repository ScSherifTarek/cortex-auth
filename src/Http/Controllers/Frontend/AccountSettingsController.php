<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Cortex\Foundation\Http\Controllers\AuthenticatedController;

class AccountSettingsController extends AuthenticatedController
{
    /**
     * Show the account update form.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $countries = countries();
        $languages = collect(languages())->pluck('name', 'iso_639_1');
        $twoFactor = $request->user($this->getGuard())->getTwoFactor();

        return view('cortex/fort::frontend.account.settings', compact('twoFactor', 'countries', 'languages'));
    }

    /**
     * Process the account update form.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $input = $request->all();
        $currentUser = $request->user($this->getGuard());
        $twoFactor = $currentUser->getTwoFactor();

        if ($input['password'] && $input['password'] !== $input['password_confirmation']) {
            return intend([
                'back' => true,
                'withInput' => $request->all(),
                'withErrors' => ['password' => trans('validation.confirmed', ['attribute' => 'password'])],
            ]);
        }

        if ($input['password'] && mb_strlen($input['password']) < config('rinvex.fort.password_min_chars')) {
            return intend([
                'back' => true,
                'withInput' => $request->all(),
                'withErrors' => ['password' => trans('validation.min.string', ['attribute' => 'password', 'min' => config('rinvex.fort.password_min_chars')])],
            ]);
        }

        if (! $input['password']) {
            unset($input['password'], $input['password_confirmation']);
        }

        $emailVerification = array_get($input, 'email') !== $currentUser->email ? [
            'email_verified' => false,
            'email_verified_at' => null,
        ] : [];

        $phoneVerification = array_get($input, 'phone') !== $currentUser->phone ? [
            'phone_verified' => false,
            'phone_verified_at' => null,
        ] : [];

        $countryVerification = array_get($input, 'country_code') !== $currentUser->country_code;

        if ($twoFactor && ($phoneVerification || $countryVerification)) {
            array_set($twoFactor, 'phone.enabled', false);
        }

        $currentUser->fill($input + $emailVerification + $phoneVerification + ['two_factor' => $twoFactor])->save();

        return intend([
            'back' => true,
            'with' => [
                          'success' => trans('cortex/fort::messages.account.'.(! empty($emailVerification) ? 'reverify' : 'updated')),
                      ] + ($twoFactor !== $currentUser->getTwoFactor() ? ['warning' => trans('cortex/fort::messages.verification.twofactor.phone.auto_disabled')] : []),
        ]);
    }
}
