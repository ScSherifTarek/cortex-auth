<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Frontend;

class PasswordResetProcessRequest extends PasswordResetRequest
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
        return ['email' => 'required|email|min:3|max:250|exists:'.config('rinvex.fort.tables.users').',email'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRedirectUrl()
    {
        return $this->redirector->getUrlGenerator()->route('frontend.passwordreset.request');
    }
}
