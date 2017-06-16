<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Backend;

use Rinvex\Fort\Models\Role;
use Rinvex\Support\Http\Requests\FormRequest;

class RoleFormRequest extends FormRequest
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
        return true;
    }

    /**
     * Process given request data before validation.
     *
     * @param array $data
     *
     * @return array
     */
    public function process($data)
    {
        // Sync abilities
        if (! empty($data['abilityList']) && $this->user()->can('grant-abilities')) {
            $data['abilities'] = $data['abilityList'];
        }

        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = $this->route('role') ?? new Role();
        $user->updateRulesUniques();

        return $user->getRules();
    }
}
