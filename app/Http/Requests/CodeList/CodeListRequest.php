<?php namespace App\Http\Requests\CodeList;

use App\Http\Requests\Request;

/**
 * Class CodeList
 * @package App\Http\Requests\User
 */
class CodeListRequest extends Request
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
        $request = Request()->all();
        $rules   = [
                    'en' => 'required|max:255|unique:'.$request['type'],
                    'fr' => 'required|max:255|unique:'.$request['type'],
                    'ar' => 'sometimes|max:255|unique:'.$request['type'],
                    ];

        if ($this->isMethod('PATCH')) {
            $rules['en'] = 'required|max:255';
            $rules['fr'] = 'required|max:255';
            $rules['ar'] = 'sometimes|max:255';
        }

        return $rules;
    }

     /**
     * Custom validation attributes
     *
     * @return string[]
     */
    public function attributes()
    {
        return [
            'en' => trans('codelist.english_translation'),
            'fr' => trans('codelist.french_translation'),
            'ar' => trans('codelist.arabic_translation')
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'en.unique' => trans('codelist.en_already_exist'),
            'fr.unique' => trans('codelist.fr_already_exist'),
            'ar.unique' => trans('codelist.ar_already_exist')       
        ];
    }
}
