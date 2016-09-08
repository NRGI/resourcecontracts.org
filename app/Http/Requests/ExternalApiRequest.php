<?php namespace App\Http\Requests;

/**
 * Class ExternalApiRequest
 * @package App\Http\Requests
 */
class ExternalApiRequest extends Request
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
        return [
            'site' => 'required|max:255',
            'url'  => 'required|url|unique:external_apis',
        ];
    }

}
