<?php namespace App\Http\Requests\Contract;

use App\Http\Requests\Request;
use App\Nrgi\Services\Language\LanguageService;
use DateTime;
use Illuminate\Http\Response;

/**
 * Class ContractRequest
 * @package App\Http\Requests\Contract
 */
class ContractRequest extends Request
{
    /**
     * Validation rules
     *
     * @param LanguageService $lang
     *
     * @return array
     */
    public function rules(LanguageService $lang)
    {

        $rules = [
            'contract_name'  => 'required',
            'country'        => 'required',
            'signature_year' => 'required|integer|digits:4',
            'file'           => 'required|mimes:pdf|max:1048576',
            'language'       => 'required',
            'resource'       => 'required',
            'category'       => 'required',
            'document_type'  => 'required',
            'signature_date' => 'date',
            'date_retrieval' => 'date',

        ];
        foreach ($this->request->get('company') as $key => $val) {
            $rules['company.'.$key.'.name']                  = 'required';
            $rules['company.'.$key.'.company_founding_date'] = 'date';
            $rules['company.'.$key.'.participation_share']   = 'numeric|min:0|max:1';
        }

        if ($this->request->get('document_type') == "Contract") {
            $rules ['type_of_contract'] = 'required';
        }

        if ($this->isMethod('PATCH')) {
            unset($rules['file']);
            $trans_code = $this->input('trans');
            if ($lang->isValidTranslationLang($trans_code)) {
                unset($rules['country'], $rules['signature_year'], $rules['language'],
                    $rules['resource'], $rules['category'], $rules['document_type']);
            }
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

                if ($this->input('signature_date')) {
                    $date = $this->input('signature_date');

                    $this->validateDate($date, $validator, trans('validation.valid_signature_date'));
                }
                if ($this->input('company')) {
                    $companies = $this->input('company');
                    foreach ($companies as $company) {
                        if (isset($company['company_founding_date'])) {
                            $this->validateDate(
                                $company['company_founding_date'],
                                $validator,
                                trans('validation.valid_incorporation_date')
                            );
                        }
                    }
                }
                if ($this->input('date_retrieval')) {
                    $date = $this->input('date_retrieval');
                    $this->validateDate($date, $validator, trans('validation.valid_retrieval_date'));
                }
                if (!$this->file('file')) {
                    return;

                }
                if ($this->file('file')->isValid()) {
                    $file            = $this->file('file');
                    $hash            = getFileHash($file->getPathName());
                    $contractService = app('App\Nrgi\Services\Contract\ContractService');

                    if ($contract = $contractService->getContractIfFileHashExist($hash)) {
                        $message = trans("validation.file_already_exists");
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
        return Response::make('Permission denied !', 403);
    }

    /**
     * Set custom messages
     * @return array
     */
    public function messages()
    {
        return [
            'file.required' => trans('validation.file_required'),
            'file.mimes'    => trans('validation.file_must_be_pdf'),
            'file.max'      => trans('validation.file_upload_limit'),
        ];
    }

    /**
     * Validate year between 1990 to 2016
     *
     * @param $date
     * @param $validator
     * @param $message
     */
    private function validateDate($date, &$validator, $message)
    {
        if ($date != '') {
            $date    = DateTime::createFromFormat("Y-m-d", $date);
            $year    = $date->format("Y");
            $nowYear = (int) date('Y');
            $message = sprintf($message, $nowYear);

            if ($year < 1900 || $year > $nowYear) {

                $validator->errors()->add('signature_date', $message);
            }
        }
    }
}
