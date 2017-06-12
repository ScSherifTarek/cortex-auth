<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Frontend;

use Illuminate\Support\Facades\Auth;
use Rinvex\Support\Http\Requests\FormRequest;

class PhoneVerificationRequest extends FormRequest
{
    /**
     * {@inheritdoc}
     */
    public function response(array $errors)
    {
        // If we've got errors, remember Two-Factor persistence
        Auth::guard()->rememberTwoFactor();

        return parent::response($errors);
    }

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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return ! $this->isMethod('post') ? [] : [
            'token' => 'required|numeric',
        ];
    }
}
