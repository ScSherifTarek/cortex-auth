<?php

declare(strict_types=1);

namespace Cortex\Fort\Http\Requests\Backend;

use Rinvex\Support\Http\Requests\FormRequest;

class RoleFormRequest extends FormRequest
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
     * Process given request data before validation.
     *
     * @param array $data
     *
     * @return array
     */
    public function process($data)
    {
        // Set abilities
        if ($this->user()->can('grant-abilities')) {
            $data['abilities'] = $data['abilities'] ?? null;
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
        $user = $this->route('role') ?? app('rinvex.fort.role');
        $user->updateRulesUniques();

        return $user->getRules();
    }
}
