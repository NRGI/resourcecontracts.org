<?php namespace App\Http\Requests\Contract;

use App\Http\Requests\Request;
use Illuminate\Http\Response;

class ContractRequest extends Request
{
    public function rules()
    {
        $rules = [
            'project_title' => 'required',
            'file'          => 'required|mimes:pdf|max:5000'
        ];

        if ($this->isMethod('PATCH')) {
            unset($rules['file']);
        }

        return $rules;
    }

    public function authorize()
    {
        return true;
    }

    public function forbiddenResponse()
    {
        return Response::make('Permission denied foo!', 403);
    }

    /**
     * Set custom messages
     * @return array
     */
    public function messages()
    {
        return [
            'file.required' => 'Contract file is required',
        ];
    }
}
