<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Frontend;

use Cortex\Fort\Models\User;

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
        $data['active'] = ! config('rinvex.fort.registration.moderated');
        $data['roles'] = [config('rinvex.fort.registration.default_role')];

        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = (new User())->getRules();
        $rules['password'] = 'required|confirmed|min:'.config('rinvex.fort.password_min_chars');

        return $rules;
    }
}
