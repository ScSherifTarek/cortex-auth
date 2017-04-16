<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Frontend;

use Illuminate\Support\Facades\Auth;

class PhoneVerificationSendRequest extends PhoneVerificationRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = $this->user() ?? Auth::guard()->attemptUser();

        return $this->isMethod('post') ? [
            'phone' => 'required|numeric|exists:'.config('rinvex.fort.tables.users').',phone,id,'.$user->id,
            'method' => 'required',
        ] : [];
    }
}
