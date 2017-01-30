<?php
namespace App\Http\Requests\Role;

use App\Http\Requests\Request;

class RoleRequest extends Request
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
        $rules = [
            'name'        => 'required|max:255',
            'permissions' => 'required',
            'description' => 'required',
        ];

        return $rules;
    }
}
