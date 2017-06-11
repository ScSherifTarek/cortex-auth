<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Frontend;

use Rinvex\Support\Http\Requests\FormRequest;

class AccountSettingsRequest extends FormRequest
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
        $country = $data['country_code'] ?? null;
        $password = $data['password'] ?? null;
        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;
        $user = $this->user();
        $twoFactor = $user->getTwoFactor();

        if (! $password) {
            unset($data['password'], $data['password_confirmation']);
        }

        if ($email !== $user->email) {
            $data['email_verified'] = false;
            $data['email_verified_at'] = null;
        }

        if ($phone !== $user->phone) {
            $data['phone_verified'] = false;
            $data['phone_verified_at'] = null;
        }

        if ($twoFactor && (isset($data['phone_verified']) || $country !== $user->country_code)) {
            array_set($twoFactor, 'phone.enabled', false);
            $data['two_factor'] = $twoFactor;
        }

        return $data;
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     *
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();
            $password = $data['password'] ?? null;

            if ($password && $password !== $data['password_confirmation']) {
                $validator->errors()->add('password', trans('validation.confirmed', ['attribute' => 'password']));
            }

            if ($password && mb_strlen($password) < config('rinvex.fort.password_min_chars')) {
                $validator->errors()->add('password', trans('validation.min.string', ['attribute' => 'password', 'min' => config('rinvex.fort.password_min_chars')]));
            }
        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = $this->user();
        $user->updateRulesUniques();

        return $user->getRules();
    }
}
