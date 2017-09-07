<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Guestarea;

use Rinvex\Fort\Exceptions\GenericException;
use Rinvex\Support\Http\Requests\FormRequest;

class PhoneVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @throws \Rinvex\Fort\Exceptions\GenericException
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();
        $attemptUser = auth()->attemptUser();

        if (empty(config('rinvex.fort.twofactor.providers'))) {
            // At least one TwoFactor provider required for phone verification
            throw new GenericException(trans('cortex/fort::messages.verification.twofactor.globaly_disabled'), ! $user ? route('guestarea.auth.login') : route('memberarea.account.settings'));
        }

        if (! $user && ! $attemptUser) {
            // User instance required to detect active TwoFactor methods
            throw new GenericException(trans('cortex/foundation::messages.session_required'), route('guestarea.auth.login'));
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
        return [];
    }
}
