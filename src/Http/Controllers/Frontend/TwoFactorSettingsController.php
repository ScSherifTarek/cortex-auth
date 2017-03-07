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
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Rinvex\Fort\Services\TwoFactorTotpProvider;
use Cortex\Foundation\Http\Controllers\AuthenticatedController;

class TwoFactorSettingsController extends AuthenticatedController
{
    /**
     * Show the Two-Factor TOTP enable form.
     *
     * @param \Illuminate\Http\Request                    $request
     * @param \Rinvex\Fort\Services\TwoFactorTotpProvider $totpProvider
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function enableTotp(Request $request, TwoFactorTotpProvider $totpProvider)
    {
        if (! in_array('totp', config('rinvex.fort.twofactor.providers'))) {
            return $this->redirect('totp');
        }

        $currentUser = $request->user($this->getGuard());
        $settings = $currentUser->getTwoFactor();

        if (array_get($settings, 'totp.enabled') && ! session()->get('success') && ! session()->get('errors')) {
            $messageBag = new MessageBag([trans('messages.verification.twofactor.totp.already')]);
            $errors = (new ViewErrorBag())->put('default', $messageBag);
        }

        if (! $secret = array_get($settings, 'totp.secret')) {
            array_set($settings, 'totp.enabled', false);
            array_set($settings, 'totp.secret', $secret = $totpProvider->generateSecretKey());

            $currentUser->fill([
                'two_factor' => $settings,
            ])->forceSave();
        }

        $qrCode = $totpProvider->getQRCodeInline(config('app.name'), $currentUser->email, $secret);

        return view('cortex/fort::frontend.account.twofactor', compact('secret', 'qrCode', 'settings', 'errors'));
    }

    /**
     * Process the Two-Factor TOTP enable form.
     *
     * @param \Illuminate\Http\Request                    $request
     * @param \Rinvex\Fort\Services\TwoFactorTotpProvider $totpProvider
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateTotp(Request $request, TwoFactorTotpProvider $totpProvider)
    {
        if (! in_array('totp', config('rinvex.fort.twofactor.providers'))) {
            return $this->redirect('totp');
        }

        $currentUser = $request->user($this->getGuard());
        $settings = $currentUser->getTwoFactor();
        $secret = array_get($settings, 'totp.secret');
        $backup = array_get($settings, 'totp.backup');
        $backupAt = array_get($settings, 'totp.backup_at');

        if ($totpProvider->verifyKey($secret, $request->get('token'))) {
            array_set($settings, 'totp.enabled', true);
            array_set($settings, 'totp.secret', $secret);
            array_set($settings, 'totp.backup', $backup ?: $this->generateTotpBackups());
            array_set($settings, 'totp.backup_at', $backupAt ?: (new Carbon())->toDateTimeString());

            // Update Two-Factor settings
            $currentUser->fill([
                'two_factor' => $settings,
            ])->forceSave();

            return intend([
                'back' => true,
                'with' => ['success' => trans('messages.verification.twofactor.totp.enabled')],
            ]);
        }

        return intend([
            'back' => true,
            'withErrors' => ['token' => trans('messages.verification.twofactor.totp.invalid_token')],
        ]);
    }

    /**
     * Process the Two-Factor TOTP disable.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function disableTotp(Request $request)
    {
        if (! in_array('totp', config('rinvex.fort.twofactor.providers'))) {
            return $this->redirect('totp');
        }

        $currentUser = $request->user($this->getGuard());
        $settings = $currentUser->getTwoFactor();

        array_set($settings, 'totp', []);

        $currentUser->fill([
            'two_factor' => $settings,
        ])->forceSave();

        return intend([
            'route' => 'frontend.account.settings',
            'with' => ['success' => trans('messages.verification.twofactor.totp.disabled')],
        ]);
    }

    /**
     * Process the Two-Factor Phone enable.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function enablePhone(Request $request)
    {
        if (! in_array('phone', config('rinvex.fort.twofactor.providers'))) {
            return $this->redirect('phone');
        }

        $currentUser = $request->user($this->getGuard());

        if (! $currentUser->phone || ! $currentUser->phone_verified) {
            return intend([
                'route' => 'frontend.account.settings',
                'withErrors' => ['phone' => trans('messages.account.phone_verification_required')],
            ]);
        }

        $settings = $currentUser->getTwoFactor();

        array_set($settings, 'phone.enabled', true);

        $currentUser->fill([
            'two_factor' => $settings,
        ])->forceSave();

        return intend([
            'route' => 'frontend.account.settings',
            'with' => ['success' => trans('messages.verification.twofactor.phone.enabled')],
        ]);
    }

    /**
     * Process the Two-Factor Phone disable.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function disablePhone(Request $request)
    {
        if (! in_array('phone', config('rinvex.fort.twofactor.providers'))) {
            return $this->redirect('phone');
        }

        $currentUser = $request->user($this->getGuard());
        $settings = $currentUser->getTwoFactor();

        array_set($settings, 'phone.enabled', false);

        $currentUser->fill([
            'two_factor' => $settings,
        ])->forceSave();

        return intend([
            'route' => 'frontend.account.settings',
            'with' => ['success' => trans('messages.verification.twofactor.phone.disabled')],
        ]);
    }

    /**
     * Process the Two-Factor OTP backup.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function backupTotp(Request $request)
    {
        if (! in_array('totp', config('rinvex.fort.twofactor.providers'))) {
            return $this->redirect('totp');
        }

        $currentUser = $request->user($this->getGuard());
        $settings = $currentUser->getTwoFactor();

        if (! array_get($settings, 'totp.enabled')) {
            return intend([
                'route' => 'frontend.account.settings',
                'withErrors' => ['rinvex.fort.verification.twofactor.totp.cant_backup' => trans('messages.verification.twofactor.totp.cant_backup')],
            ]);
        }

        array_set($settings, 'totp.backup', $this->generateTotpBackups());
        array_set($settings, 'totp.backup_at', (new Carbon())->toDateTimeString());

        $currentUser->fill([
            'two_factor' => $settings,
        ])->forceSave();

        return intend([
            'back' => true,
            'with' => ['success' => trans('messages.verification.twofactor.totp.rebackup')],
        ]);
    }

    /**
     * Generate Two-Factor OTP backup codes.
     *
     * @return array
     */
    protected function generateTotpBackups()
    {
        $backup = [];

        for ($x = 0; $x <= 9; $x++) {
            $backup[] = str_pad(random_int(0, 9999999999), 10, 0, STR_PAD_BOTH);
        }

        return $backup;
    }

    /**
     * Return redirect response.
     *
     * @param string $twofactorProvider
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function redirect($twofactorProvider)
    {
        return intend([
            'route' => 'frontend.account.settings',
            'withErrors' => ['token' => trans("messages.verification.twofactor.{$twofactorProvider}.globaly_disabled")],
        ]);
    }
}
