<?php namespace App\Http\Requests\Contract;

use App\Http\Requests\Request;
use Illuminate\Http\Response;

/**
 * Class ContractRequest
 * @package App\Http\Requests\Contract
 */
class ContractRequest extends Request
{
    /**
     * Validation rules
     * @return array
     */
    public function rules()
    {
        $rules = [
            'contract_name'       => 'required',
            'country'             => 'required',
            'signature_year'      => 'required|integer|digits:4',
            'file'                => 'required|mimes:pdf|max:1048576',
            'participation_share' => 'numeric|min:0|max:1',
            'language'            => 'required',
            'resource'            => 'required',
            'category'            => 'required',
            'document_type'       => 'required'
        ];
        foreach ($this->request->get('company') as $key => $val) {
            $rules['company.' . $key . '.name'] = 'required';
        }

        if($this->request->get('document_type') == "Contract")
        {
            $rules ['type_of_contract'] = 'required';
        }

        if ($this->isMethod('PATCH')) {
            unset($rules['file']);
        }
        dd($rules);
        return $rules;
    }

    /**
     * Validate for unique file hash
     * @return \Illuminate\Validation\Validator
     */
    public function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->after(
            function () use ($validator) {

                if (!$this->file('file')) {
                    return;
                }
                if ($this->file('file')->isValid()) {
                    $file            = $this->file('file');
                    $hash            = getFileHash($file->getPathName());
                    $contractService = app('App\Nrgi\Services\Contract\ContractService');

                    if ($contract = $contractService->getContractIfFileHashExist($hash)) {
                        $message = trans(
                            "The contract file is already present in our system. Please check the following Title of contract with which the uploaded file is linked and make necessary updates."
                        );
                        $message .= sprintf(
                            "<div><a target='_blank' href='%s'>%s</a></div>",
                            route('contract.show', $contract->id),
                            $contract->title
                        );

                        $validator->errors()->add('file', $message);
                    }
                } else {
                    $validator->errors()->add('file', $this->file('file')->getError());
                }

            }
        );

        return $validator;
    }

    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return mixed
     */
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
            'file.required' => trans('Contract file is required.'),
            'file.mimes'    => trans('The file must be a pdf.'),
            'file.max'      => trans('You can upload file upto 1GB only.')
        ];
    }
}
