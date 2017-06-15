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
        return [
            'email' => 'required|email|min:3|max:250|exists:'.config('rinvex.fort.tables.users').',email',
            'password' => 'required|confirmed|min:'.config('rinvex.fort.password_min_chars')
        ];
    }
}
