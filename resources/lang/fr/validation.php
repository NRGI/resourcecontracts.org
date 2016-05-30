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

    "accepted"             => "L'attribut: doit être accepté.",
    "active_url"           => "L'attribut: n'est pas une URL valide.",
    "after"                => "L'attribut: doit avoir une date après: date.",
    "alpha"                => "L'attribut: peut seulement contenir des lettres.",
    "alpha_dash"           => "L'attribut: peut seulement contenir des lettres, des chiffres et des tirets.",
    "alpha_num"            => "L'attribut: peut seulement contenir des lettres et des chiffres.",
    "array"                => "L'attribut: doit être une série.",
    "before"               => "L'attribut: doit avoir une date avant: date.",
    "between"              => [
        "numeric" => "L'attribut: doit être entre: min et :max.",
        "file"    => "L'attribut: doit être entre: min et :max kilo-octets.",
        "string"  => "L'attribut: doit être entre: min et :max caractères.",
        "array"   => "L'attribut: doit être entre: min et :max points.",
    ],
    "boolean"              => "Le champ d'attribut: doit être vrai ou faux.",
    "confirmed"            => "La confirmation de l'attribut: ne correpond pas.",
    "date"                 => "L'attribut: n'est pas une date valide.",
    "date_format"          => "L'attribut: ne correspond pas au format :format.",
    "different"            => "L'attribut: et :autre doivent être différents.",
    "digits"               => "L'attribut: doit être: chiffres chiffres.",
    "digits_between"       => "L'attribut: doit être entre :min et :max chiffres.",
    "email"                => "L'attribut: doit être une adresse email valide.",
    "filled"               => "Le champ d'attribut: est requis.",
    "exists"               => "L'attribut: sélectionné est invalide.",
    "image"                => "L'attribut: doit être une image.",
    "in"                   => "L'attribut: sélectionné est invalide.",
    "integer"              => "L'attribut: doit être un nombre entier.",
    "ip"                   => "L'attribut: doit être une adresse IP valide.",
    "max"                  => [
        "numeric" => "L'attribut: ne doit pas excéder :max.",
        "file"    => "L'attribut: ne doit pas excéder :max kilo-octets.",
        "string"  => "L'attribut: ne doit pas excéder :max caractères.",
        "array"   => "L'attribut: ne doit pas excéder :max points.",
    ],
    "mimes"                => "L'attribut: doit être un fichier de type: :valeurs.",
    "min"                  => [
        "numeric" => "L'attribut: doit être au minimum :min.",
        "file"    => "L'attribut: doit être au minimum :min kilo-octets.",
        "string"  => "L'attribut: doit être au minimum :min caractères.",
        "array"   => "L'attribut: doit être au minimum :min points.",
    ],
    "not_in"               => "L'attribut: sélectionné est invalide.",
    "numeric"              => "L'attribut: doit être un nombre.",
    "regex"                => "Le format de l'attribut: est invalide.",
    "required"             => "Le champ d'attribut: est requis.",
    "required_if"          => "Le champ d'attribut: est requis quand :autre est :valeur.",
    "required_with"        => "Le champ d'attribut: est requis quand :valeur est présente.",
    "required_with_all"    => "Le champ d'attribut: est requis quand :valeur est présente.",
    "required_without"     => "Le champ d'attribut: est requis quand :valeur n'est pas présente.",
    "required_without_all" => "Le champ d'attribut: est requis quand  aucune des :valeurs n'est présente.",
    "same"                 => "L'attribut: et :autre doivent correspondre.",
    "size"                 => [
        "numeric" => "L'attribut: doit être :taille.",
        "file"    => "L'attribut: doit être :taille en kilo-octets.",
        "string"  => "L'attribut: doit être :taille en caractères.",
        "array"   => "L'attribut: doit être :taille en points.",
    ],
    "unique"               => "L'attribut: a déjà été pris.",
    "url"                  => "Le format de l'attribut: est invalide.",
    "timezone"             => "L'attribut: doit être une zone valide.",
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
            'rule-name' => 'message personnalisé',
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
