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
        $countries = array_map(function ($country) {
            return $country['name'];
        }, countries());
        $twoFactor = $request->user($this->getGuard())->getTwoFactor();

        return view('cortex/fort::frontend.account.settings', compact('twoFactor', 'countries'));
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

        $countryVerification = array_get($input, 'country') !== $currentUser->country;

        if ($twoFactor && ($phoneVerification || $countryVerification)) {
            array_set($twoFactor, 'two_factor.phone.enabled', false);
        }

        $currentUser->fill($input + $emailVerification + $phoneVerification + $twoFactor)->save();

        return intend([
            'back' => true,
            'with' => [
                          'success' => trans('messages.account.'.(! empty($emailVerification) ? 'reverify' : 'updated')),
                      ] + ($twoFactor !== $currentUser->getTwoFactor() ? ['warning' => trans('messages.verification.twofactor.phone.auto_disabled')] : []),
        ]);
    }
}
