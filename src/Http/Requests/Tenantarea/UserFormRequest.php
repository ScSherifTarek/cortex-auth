<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Tenantarea;

use Carbon\Carbon;
use Rinvex\Support\Http\Requests\FormRequest;

class UserFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Process given request data before validation.
     *
     * @param array $data
     *
     * @return array
     */
    public function process($data)
    {
        $owner = optional(optional(config('rinvex.tenants.tenant.active'))->owner)->id;
        $user = $this->route('user') ?? app('rinvex.fort.user');
        $country = $data['country_code'] ?? null;
        $twoFactor = $user->getTwoFactor();

        $data['email_verified'] = $this->get('email_verified', false);
        $data['phone_verified'] = $this->get('phone_verified', false);

        if ($user->exists && empty($data['password'])) {
            unset($data['password'], $data['password_confirmation']);
        }

        // Update email verification date
        if ($data['email_verified'] && $user->email_verified !== $data['email_verified']) {
            $data['email_verified_at'] = Carbon::now();
        }

        // Update phone verification date
        if ($data['phone_verified'] && $user->phone_verified !== $data['phone_verified']) {
            $data['phone_verified_at'] = Carbon::now();
        }

        // Set abilities
        if ($this->user()->can('grant-abilities') && $data['abilities']) {
            $data['abilities'] = $this->user()->id === $owner
                ? array_intersect(app('rinvex.fort.role')->forAllTenants()->where('slug', 'manager')->first()->abilities->pluck('id')->toArray(), $data['abilities'])
                : array_intersect($this->user()->allAbilities->pluck('id')->toArray(), $data['abilities']);
        } else {
            unset($data['abilities']);
        }

        // Set roles
        if ($this->user()->can('assign-roles') && $data['roles']) {
            $data['roles'] = $this->user()->id === $owner
                ? array_intersect(app('rinvex.fort.role')->all()->pluck('id')->toArray(), $data['roles'])
                : array_intersect($this->user()->roles->pluck('id')->toArray(), $data['roles']);
        } else {
            unset($data['roles']);
        }

        if ($twoFactor && (isset($data['phone_verified_at']) || $country !== $user->country_code)) {
            array_set($twoFactor, 'phone.enabled', false);
            $data['two_factor'] = $twoFactor;
        }

        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = $this->route('user') ?? app('rinvex.fort.user');
        $user->updateRulesUniques();
        $rules = $user->getRules();
        $rules['password'] = $user->exists
            ? 'confirmed|min:'.config('rinvex.fort.password_min_chars')
            : 'required|confirmed|min:'.config('rinvex.fort.password_min_chars');

        return $rules;
    }
}
