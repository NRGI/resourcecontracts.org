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

    "accepted"             => "L' :attribute doit être accepté.",
    "active_url"           => "L' :attribute n'est pas une URL valide.",
    "after"                => "L' :attribute doit avoir une date après :date.",
    "alpha"                => "L' :attribute peut seulement contenir des lettres.",
    "alpha_dash"           => "L' :attribute peut seulement contenir des lettres, des chiffres et des tirets.",
    "alpha_num"            => "L' :attribute peut seulement contenir des lettres et des chiffres.",
    "array"                => "L' :attribute doit être une série.",
    "before"               => "L' :attribute doit avoir une date avant :date.",
    "between"              => [
        "numeric" => "L' :attribute doit être entre :min et :max.",
        "file"    => "L' :attribute doit être entre :min et :max kilo-octets.",
        "string"  => "L' :attribute doit être entre :min et :max caractères.",
        "array"   => "L' :attribute doit être entre :min et :max points.",
    ],
    "boolean"              => "Le champ d' :attribute doit être vrai ou faux.",
    "confirmed"            => "La confirmation de l' :attribute ne correpond pas.",
    "date"                 => "L' :attribute n'est pas une date valide.",
    "date_format"          => "L' :attribute ne correspond pas au format :format.",
    "different"            => "L' :attribute et :other doivent être différents.",
    "digits"               => "L' :attribute doit être :digits chiffres.",
    "digits_between"       => "L' :attribute doit être entre :min et :max chiffres.",
    "email"                => "L' :attribute doit être une adresse email valide.",
    "filled"               => "Le champ d' :attribute est requis.",
    "exists"               => "L' :attribute sélectionné est invalide.",
    "image"                => "L' :attribute doit être une image.",
    "in"                   => "L' :attribute sélectionné est invalide.",
    "integer"              => "L' :attribute doit être un nombre entier.",
    "ip"                   => "L' :attribute doit être une adresse IP valide.",
    "max"                  => [
        "numeric" => "L' :attribute ne doit pas excéder :max.",
        "file"    => "L' :attribute ne doit pas excéder :max kilo-octets.",
        "string"  => "L' :attribute ne doit pas excéder :max caractères.",
        "array"   => "L' :attribute ne doit pas excéder :max points.",
    ],
    "mimes"                => "L' :attribute doit être un fichier de type: :values.",
    "min"                  => [
        "numeric" => "L' :attribute doit être au minimum :min.",
        "file"    => "L' :attribute doit être au minimum :min kilo-octets.",
        "string"  => "L' :attribute doit être au minimum :min caractères.",
        "array"   => "L' :attribute doit être au minimum :min points.",
    ],
    "not_in"               => "L' :attribute sélectionné est invalide.",
    "numeric"              => "L' :attribute doit être un nombre.",
    "regex"                => "Le format de l' :attribute est invalide.",
    "required"             => "Le champ d' :attribute est requis.",
    "required_if"          => "Le champ d' :attribute est requis quand :other est :value.",
    "required_with"        => "Le champ d' :attribute est requis quand :value est présente.",
    "required_with_all"    => "Le champ d' :attribute est requis quand :value est présente.",
    "required_without"     => "Le champ d' :attribute est requis quand :value n'est pas présente.",
    "required_without_all" => "Le champ d' :attribute est requis quand  aucune des :values n'est présente.",
    "same"                 => "L' :attribute et :other doivent correspondre.",
    "size"                 => [
        "numeric" => "L' :attribute doit être :size.",
        "file"    => "L' :attribute doit être :size en kilo-octets.",
        "string"  => "L' :attribute doit être :size en caractères.",
        "array"   => "L' :attribute doit être :size en points.",
    ],
    "unique"               => "L' :attribute a déjà été pris.",
    "url"                  => "Le format de l' :attribute est invalide.",
    "timezone"             => "L' :attribute doit être une zone valide.",
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

    'custom' => [
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

    'attributes'          => [],
    'file_already_exists' => 'Le dossier du contrat est déjà présent dans notre système . S\'il vous plaît vérifier le titre suivant des contrat avec lequel le fichier téléchargé est lié et faire les mises à jour nécessaires',
    'file_required'       => 'fichier de contrat est nécessaire.',
    'file_must_be_pdf'    => 'Le fichier doit être un pdf.',
    'file_upload_limit'   => 'Vous pouvez télécharger le fichier jusqu\'à 1 Go seulement.',
];
