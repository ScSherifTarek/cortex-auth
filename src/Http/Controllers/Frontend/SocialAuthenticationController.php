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

use Exception;
use Rinvex\Fort\Models\Role;
use Rinvex\Fort\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthenticationController extends AuthenticationController
{
    /**
     * Redirect to Github for authentication.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Handle Github authentication callback.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Rinvex\Fort\Models\User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGithubCallback(Request $request, User $user)
    {
        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (Exception $e) {
            return intend([
                'route' => 'frontend.auth.social.github',
            ]);
        }

        $result = User::whereHas(['socialites', function ($query) use ($githubUser) {
            $query->where('provider', 'github');
            $query->where('provider_uid', $githubUser->id);
        }])->first();

        if (! $result) {
            // Prepare registration data
            $input = [
                'email' => $githubUser->email,
                'username' => $githubUser->username,
                'password' => str_random(),
                'active' => ! config('rinvex.fort.registration.moderated'),
            ];

            // Fire the register start event
            event('rinvex.fort.register.social.start', [$input]);

            // Create user
            $result = $user->create($input);

            // Attach default role to the registered user
            if ($defaultRole = config('rinvex.fort.registration.default_role')) {
                if ($defaultRole = Role::where('slug', $defaultRole)->first()) {
                    $result->roles()->attach($defaultRole);
                }
            }

            // Fire the register success event
            event('rinvex.fort.register.social.success', [$result]);

            $result->socialites()->create([
                'user_id' => 'github',
                'provider' => 'github',
                'provider_uid' => $githubUser->id,
            ]);
        }

        $result = Auth::guard($this->getGuard())->login($result, true);

        return $this->getLoginResponse($request, $result);
    }
}
