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

namespace Cortex\Fort\Http\Requests\Frontend;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

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
