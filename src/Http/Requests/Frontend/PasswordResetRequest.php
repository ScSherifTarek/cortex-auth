<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Frontend;

use Rinvex\Support\Http\Requests\FormRequest;

class PasswordResetRequest extends FormRequest
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
        return $this->isMethod('post') ? [
            'token' => 'required|regex:/^[0-9a-zA-Z]+$/',
            'email' => 'required|email|max:250|exists:'.config('rinvex.fort.tables.users').',email',
            'password' => 'required|confirmed|min:'.config('rinvex.fort.password_min_chars'),
        ] : [
            'token' => 'required|regex:/^[0-9a-zA-Z]+$/',
            'email' => 'required|email|max:250|exists:'.config('rinvex.fort.tables.users').',email',
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

        return $this->redirector->getUrlGenerator()->route('frontend.passwordreset.request');
    }
}
