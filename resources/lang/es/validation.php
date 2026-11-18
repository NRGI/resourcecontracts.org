<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    "accepted"                 => "Debes aceptar el campo :attribute.",
    "active_url"               => "El campo :attribute no es una URL válida.",
    "after"                    => "El campo :attribute debe ser una fecha posterior a :date.",
    "alpha"                    => "El campo :attribute solo puede contener letras.",
    "alpha_dash"               => "El campo :attribute solo puede contener letras, números y guiones.",
    "alpha_num"                => "El campo :attribute solo puede contener letras y números.",
    "array"                    => "El campo :attribute debe ser una lista.",
    "before"                   => "El campo :attribute debe ser una fecha anterior a :date.",
    "between"                  => [
        "numeric" => "El campo :attribute debe estar entre :min y :max.",
        "file"    => "El campo :attribute debe tener entre :min y :max kilobytes.",
        "string"  => "El campo :attribute debe tener entre :min y :max caracteres.",
        "array"   => "El campo :attribute debe tener entre :min y :max elementos.",
    ],
    "boolean"                  => "El campo :attribute debe ser verdadero o falso.",
    "confirmed"                => "La confirmación del campo :attribute no coincide.",
    "date"                     => "El campo :attribute no es una fecha válida.",
    "date_format"              => "El campo :attribute no coincide con el formato :format.",
    "different"                => "Los campos :attribute y :other deben ser diferentes.",
    "digits"                   => "El campo :attribute debe tener :digits dígitos.",
    "digits_between"           => "El campo :attribute debe tener entre :min y :max dígitos.",
    "email"                    => "El campo :attribute debe ser una dirección de correo electrónico válida.",
    "filled"                   => "El campo :attribute es obligatorio.",
    "exists"                   => "El valor seleccionado en :attribute no es válido.",
    "image"                    => "El campo :attribute debe ser una imagen.",
    "in"                       => "El valor seleccionado en :attribute no es válido.",
    "integer"                  => "El campo :attribute debe ser un número entero.",
    "ip"                       => "El campo :attribute debe ser una dirección IP válida.",
    "max"                      => [
        "numeric" => "El campo :attribute no puede ser mayor que :max.",
        "file"    => "El campo :attribute no puede superar los :max kilobytes.",
        "string"  => "El campo :attribute no puede superar los :max caracteres.",
        "array"   => "El campo :attribute no puede tener más de :max elementos.",
    ],
    "mimes"                    => "El campo :attribute debe ser un archivo de tipo: :values.",
    "min"                      => [
        "numeric" => "El campo :attribute debe ser como mínimo :min.",
        "file"    => "El campo :attribute debe tener como mínimo :min kilobytes.",
        "string"  => "El campo :attribute debe tener como mínimo :min caracteres.",
        "array"   => "El campo :attribute debe tener como mínimo :min elementos.",
    ],
    "not_in"                   => "El valor seleccionado en :attribute no es válido.",
    "numeric"                  => "El campo :attribute debe ser un número.",
    "regex"                    => "El formato del campo :attribute no es válido.",
    "required"                 => "El campo :attribute es obligatorio.",
    "required_if"              => "El campo :attribute es obligatorio cuando :other es :value.",
    "required_with"            => "El campo :attribute es obligatorio cuando :values está presente.",
    "required_with_all"        => "El campo :attribute es obligatorio cuando :values está presente.",
    "required_without"         => "El campo :attribute es obligatorio cuando :values no está presente.",
    "required_without_all"     => "El campo :attribute es obligatorio cuando no está presente ninguno de los valores :values.",
    "same"                     => "Los campos :attribute y :other deben coincidir.",
    "size"                     => [
        "numeric" => "El campo :attribute debe ser :size.",
        "file"    => "El campo :attribute debe tener :size kilobytes.",
        "string"  => "El campo :attribute debe tener :size caracteres.",
        "array"   => "El campo :attribute debe contener :size elementos.",
    ],
    "unique"                   => "El valor del campo :attribute ya está en uso.",
    "url"                      => "El formato del campo :attribute no es válido.",
    "timezone"                 => "El campo :attribute debe ser una zona horaria válida.",
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom'                   => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes'               => [],
    'file_already_exists'      => 'El archivo del contrato ya existe en nuestro sistema. Comprueba el siguiente título del contrato al que está vinculado el archivo subido y haz las actualizaciones necesarias.',
    'file_required'            => 'El archivo del contrato es obligatorio.',
    'file_must_be_pdf'         => 'El archivo debe ser un PDF.',
    'file_upload_limit'        => 'Solo puedes subir archivos de hasta 1 GB.',
    'valid_signature_date'     => 'El año de la fecha de firma debe estar entre 1900 y %s',
    'valid_incorporation_date' => 'El año de la fecha de constitución debe estar entre 1900 y %s',
    'valid_retrieval_date'     => 'El año de la fecha de obtención debe estar entre 1900 y %s'
];
