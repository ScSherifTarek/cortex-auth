<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Guestarea;

use Rinvex\Fort\Exceptions\GenericException;
use Rinvex\Support\Http\Requests\FormRequest;

class RegistrationRequest extends FormRequest
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
        if (! config('rinvex.fort.registration.enabled')) {
            throw new GenericException(trans('cortex/fort::messages.register.disabled'));
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
