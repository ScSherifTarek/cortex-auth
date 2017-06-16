<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Frontend;

class PasswordResetPostProcessRequest extends PasswordResetRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Do not validate `token` here since at this stage we can NOT generate viewable
        // notification/error, and it is been processed through PasswordResetBroker anyway
        return [
            'email' => 'required|email|min:3|max:250|exists:'.config('rinvex.fort.tables.users').',email',
            'password' => 'required|confirmed|min:'.config('rinvex.fort.password_min_chars'),
        ];
    }
}
