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

use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Skip validation rules for request validation form
        if ($this->route()->getName() == 'frontend.verification.email.request') {
            return [];
        }

        return $this->isMethod('post') ? [
            'email' => 'required|email|max:255|exists:'.config('rinvex.fort.tables.users').',email',
        ] : [
            'token' => 'required|regex:/^[0-9a-zA-Z]+$/',
            'email' => 'required|email|max:255|exists:'.config('rinvex.fort.tables.users').',email',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRedirectUrl()
    {
        if ($this->isMethod('post')) {
            return parent::getRedirectUrl();
        }

        return $this->redirector->getUrlGenerator()->route('frontend.verification.email.request');
    }
}
