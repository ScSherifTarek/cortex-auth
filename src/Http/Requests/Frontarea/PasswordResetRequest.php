<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Frontarea;

use Illuminate\Foundation\Http\FormRequest;
use Rinvex\Fort\Exceptions\GenericException;

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
            throw new GenericException(trans('cortex/fort::messages.passwordreset.already_logged'), route('frontarea.account.settings').'#security-tab');
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