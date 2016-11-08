<?php namespace App\Http\Requests\User;

use App\Http\Requests\Request;

/**
 * Class UserRequest
 * @package App\Http\Requests\User
 */
class UserRequest extends Request
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
            'name'     => 'required|max:255',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
            'role'     => 'required|exists:roles,name',
            'status'   => 'required'
        ];

        if (!empty($this->input('role')) && in_array($this->input('role'),config('nrgi.country_role')) ) {
            $rules['country.0'] = 'required';
        }

        if ($this->isMethod('PATCH')) {
            if (empty($this->input('password'))) {
                unset($rules['password']);
            }
            unset($rules['email']);
        }

        return $rules;

    }

    /**
     * custom message
     * @return array
     */
    public function messages()
    {
        return [
            'country.0.required' => 'The country field is required.'
        ];
    }
}
