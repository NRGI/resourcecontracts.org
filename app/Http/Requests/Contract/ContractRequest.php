<?php namespace App\Http\Requests\Contract;

use App\Http\Requests\Request;
use Illuminate\Http\Response;

class ContractRequest extends Request
{
    public function rules()
    {
        $rules = [
            'contract_name'  => 'required',
            'signature_date' => 'required',
            'country'        => 'required',
            'file'           => 'required|mimes:pdf|max:51200'
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
            'file.required' => 'Contract file is required.',
            'file.mimes'    => 'The file must be a pdf.',
            'file.max'      => 'You can upload file upto 50MB only.'
        ];
    }
}
