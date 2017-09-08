<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Guestarea;

class RegistrationProcessRequest extends RegistrationRequest
{
    /**
     * Process given request data before validation.
     *
     * @param array $data
     *
     * @return array
     */
    public function process($data)
    {
        $role = app('rinvex.fort.role')->where('slug', config('rinvex.fort.registration.default_role'))->first();
        $data['is_active'] = ! config('rinvex.fort.registration.moderated');
        ! $role || $data['roles'] = [$role->id];

        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = app('rinvex.fort.user')->getRules();
        $rules['password'] = 'required|confirmed|min:'.config('rinvex.fort.password_min_chars');

        return $rules;
    }
}