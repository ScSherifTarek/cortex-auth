<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Frontarea;

use Rinvex\Fort\Exceptions\GenericException;
use Rinvex\Support\Http\Requests\FormRequest;

class TwoFactorTotpSettingsRequest extends FormRequest
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
        if (! in_array('totp', config('rinvex.fort.twofactor.providers'))) {
            throw new GenericException(trans('cortex/fort::messages.verification.twofactor.totp.globaly_disabled'), route('frontarea.account.settings'));
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
