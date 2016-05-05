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

    "accepted"             => "es The :attribute must be accepted.",
    "active_url"           => "es The :attribute is not a valid URL.",
    "after"                => "es The :attribute must be a date after :date.",
    "alpha"                => "es The :attribute may only contain letters.",
    "alpha_dash"           => "es The :attribute may only contain letters, numbers, and dashes.",
    "alpha_num"            => "es The :attribute may only contain letters and numbers.",
    "array"                => "es The :attribute must be an array.",
    "before"               => "es The :attribute must be a date before :date.",
    "between"              => [
        "numeric" => "es The :attribute must be between :min and :max.",
        "file"    => "es The :attribute must be between :min and :max kilobytes.",
        "string"  => "es The :attribute must be between :min and :max characters.",
        "array"   => "es The :attribute must have between :min and :max items.",
    ],
    "boolean"              => "es The :attribute field must be true or false.",
    "confirmed"            => "es The :attribute confirmation does not match.",
    "date"                 => "es The :attribute is not a valid date.",
    "date_format"          => "es The :attribute does not match the format :format.",
    "different"            => "es The :attribute and :other must be different.",
    "digits"               => "es The :attribute must be :digits digits.",
    "digits_between"       => "es The :attribute must be between :min and :max digits.",
    "email"                => "es The :attribute must be a valid email address.",
    "filled"               => "es The :attribute field is required.",
    "exists"               => "es The selected :attribute is invalid.",
    "image"                => "es The :attribute must be an image.",
    "in"                   => "es The selected :attribute is invalid.",
    "integer"              => "es The :attribute must be an integer.",
    "ip"                   => "es The :attribute must be a valid IP address.",
    "max"                  => [
        "numeric" => "es The :attribute may not be greater than :max.",
        "file"    => "es The :attribute may not be greater than :max kilobytes.",
        "string"  => "es The :attribute may not be greater than :max characters.",
        "array"   => "es The :attribute may not have more than :max items.",
    ],
    "mimes"                => "es The :attribute must be a file of type: :values.",
    "min"                  => [
        "numeric" => "es The :attribute must be at least :min.",
        "file"    => "es The :attribute must be at least :min kilobytes.",
        "string"  => "es The :attribute must be at least :min characters.",
        "array"   => "es The :attribute must have at least :min items.",
    ],
    "not_in"               => "es The selected :attribute is invalid.",
    "numeric"              => "es The :attribute must be a number.",
    "regex"                => "es The :attribute format is invalid.",
    "required"             => "es The :attribute field is required.",
    "required_if"          => "es The :attribute field is required when :other is :value.",
    "required_with"        => "es The :attribute field is required when :values is present.",
    "required_with_all"    => "es The :attribute field is required when :values is present.",
    "required_without"     => "es The :attribute field is required when :values is not present.",
    "required_without_all" => "es The :attribute field is required when none of :values are present.",
    "same"                 => "es The :attribute and :other must match.",
    "size"                 => [
        "numeric" => "es The :attribute must be :size.",
        "file"    => "es The :attribute must be :size kilobytes.",
        "string"  => "es The :attribute must be :size characters.",
        "array"   => "es The :attribute must contain :size items.",
    ],
    "unique"               => "es The :attribute has already been taken.",
    "url"                  => "es The :attribute format is invalid.",
    "timezone"             => "es The :attribute must be a valid zone.",
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

    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'es custom-message',
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

    'attributes'           => [],

];
