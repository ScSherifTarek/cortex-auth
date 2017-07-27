<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Frontend;

use Rinvex\Fort\Exceptions\GenericException;
use Rinvex\Support\Http\Requests\FormRequest;

class PasswordResetRequest extends FormRequest
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
        if ($this->user()) {
            throw new GenericException(trans('cortex/fort::messages.passwordreset.already_logged'), route('userarea.account.settings').'#security-tab');
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
