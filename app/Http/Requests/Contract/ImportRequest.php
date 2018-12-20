<?php namespace App\Http\Requests\Contract;

use App\Http\Requests\Request;

/**
 * Class ImportRequest
 * @package App\Http\Requests
 */
class ImportRequest extends Request
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
            'file' => 'required|max:51200',
        ];
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

                if (!is_null($this->file('file')) && $this->file('file')->isValid()) {
                    $file           = $this->file('file');
                    $csv_mime_types = [
                        'text/csv',
                        'text/plain',
                        'application/csv',
                        'text/comma-separated-values',
                        'application/excel',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.msexcel',
                        'text/anytext',
                        'application/octet-stream',
                        'application/txt',
                    ];

                    if (!in_array($file->getClientMimeType(), $csv_mime_types)) {
                        $validator->errors()->add('file', 'The file must be a CSV or Excel.');
                    }
                }
            }
        );

        return $validator;
    }

}
