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
            'file'                => 'required|mimes:pdf|max:51200',
            'participation_share' => 'numeric|min:0|max:1'
        ];

        if ($this->isMethod('PATCH')) {
            unset($rules['file']);
        }

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

                if ($this->isMethod('POST')) {

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
            'file.max'      => trans('You can upload file upto 50MB only.')
        ];
    }
}
