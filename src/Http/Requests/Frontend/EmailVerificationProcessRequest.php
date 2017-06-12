<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Frontend;

use Cortex\Fort\Models\User;
use Rinvex\Support\Http\Requests\FormRequest;
use Cortex\Foundation\Exceptions\GenericException;

class EmailVerificationProcessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @throws \Cortex\Foundation\Exceptions\GenericException
     *
     * @return bool
     */
    public function authorize()
    {
        $userVerified = $this->user() && $this->user()->email_verified;
        $guestVerified = ($email = $this->get('email')) && ($user = User::where('email', $email)->first()) && $user->email_verified;

        if ($userVerified || $guestVerified) {
            // Redirect users if their email already verified, no need to process their request
            throw new GenericException(trans('cortex/fort::messages.verification.email.already'), $userVerified ? route('frontend.account.settings') : route('frontend.auth.login'));
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Do not validate `token` here since at this stage we can NOT generate viewable
        // notification/error, and it is been processed through EmailVerificationBroker anyway
        return ['email' => 'required|email|max:250|exists:'.config('rinvex.fort.tables.users').',email'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRedirectUrl()
    {
        // This is route('frontend.verification.email.send')
        if ($this->isMethod('post')) {
            return parent::getRedirectUrl();
        }

        // This is route('frontend.verification.email.verify')
        return $this->redirector->getUrlGenerator()->route('frontend.verification.email.request');
    }
}
