<?php namespace App\Http\Requests\Utility;

use App\Http\Requests\Request;

/**
 * Class UserRequest
 * @package App\Http\Requests\User
 */
class UtilityRequest extends Request
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
            'category'     => 'required',
            'country'    => 'required',

        ];
        return $rules;

    }
}
