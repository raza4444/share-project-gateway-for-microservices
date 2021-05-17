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

    'accepted' => 'The :attribute moet worden aanvaard.',
    'active_url' => 'The :attribute is geen geldige URL.',
    'after' => 'The :attribute:attribute moet een datum na te zijn.',
    'after_or_equal' => 'The :attribute moet een datum na of gelijk aan: date.',
    'alpha' => 'The :attribute mag alleen letters.',
    'alpha_dash' => 'The :attribute mag alleen letters, cijfers, streepjes en underscores.',
    'alpha_num' => 'The :attribute mag alleen letters en cijfers bevatten.',
    'array' => 'Het :attribute moet een array zijn.',
    'before' => 'The :attribute:attribute moet een datum voor zijn.',
    'before_or_equal' => 'The :attribute moet een datum voor of gelijk aan: date.',
    'between' => [
        'numeric' => 'De :attribute moet tussen :min :max.',
        'file' => 'De :attribute moet tussen :min :max kilobytes.',
        'string' => 'De :attribute moet tussen :min :max karakters.',
        'array' => 'The :min en :max items kenmerk moet hebben tussen.',
    ],
    'boolean' => 'The :attribute veld moet waar of onwaar zijn.',
    'confirmed' => 'The :attribute attribuut komt niet overeen.',
    'date' => 'The :attribute is geen geldige datum.',
    'date_equals' => 'The :attribute:attribute moet een datum gelijk te zijn.',
    'date_format' => 'The :attribute komt niet overeen met het formaat :size.',
    'different' => 'The :attribute en :other moeten verschillend zijn.',
    'digits' => 'The :attribute moet zijn :digits cijfers.',
    'digits_between' => 'De :attribute moet tussen :min :max cijfer.',
    'dimensions' => 'The :attribute ongeldige image dimensies.',
    'distinct' => 'The :attribute veld heeft een dubbele waarde.',
    'email' => 'The :attribute moet een geldig e-mailadres zijn.',
    'ends_with' => 'Het :attribute moet eindigen met een van de volgende: :values.',
    'exists' => 'De geselecteerde :attribute is ongeldig.',
    'file' => 'The :attribute moet een bestand zijn.',
    'filled' => 'The :attribute veld moet een waarde hebben.',
    'gt' => [
        'numeric' => 'The :attribute :attribute moet groter zijn dan.',
        'file' => 'The :attribute moet groter zijn dan :value kilobytes.',
        'string' => 'The :attribute moet groter zijn dan :value karakters.',
        'array' => 'The :value items :attribute moet meer zijn dan te hebben.',
    ],
    'gte' => [
        'numeric' => 'De :value :attribute moet groter zijn dan of gelijk zijn.',
        'file' => 'Het :attribute groter dan of gelijk :value kilobytes.',
        'string' => 'De :value karakters :attribute moet groter zijn dan of gelijk zijn.',
        'array' => 'The :attribute moet :value items of meer.',
    ],
    'image' => 'The :attribute moet een beeld zijn.',
    'in' => 'De geselecteerde :attribute is ongeldig.',
    'in_array' => 'The :attribute veld bestaat niet in :other.',
    'integer' => 'Het :attribute moet een geheel getal zijn.',
    'ip' => 'The :attribute moet een geldig IP-adres zijn.',
    'ipv4' => 'Het :attribute moet een geldig IPv4-adres zijn.',
    'ipv6' => 'The :attribute moet een geldig IPv6-adres zijn.',
    'json' => 'The :attribute moet een geldige JSON string.',
    'lt' => [
        'numeric' => 'The :attribute moet lager zijn dan: waarde.',
        'file' => 'The :attribute moet kleiner zijn dan :value kilobytes.',
        'string' => 'The :attribute moet minder dan :value karakters.',
        'array' => 'The :value items :attribute moet minder dan hebben.',
    ],
    'lte' => [
        'numeric' => 'Het :attribute moet minder bedragen dan of gelijk zijn :value.',
        'file' => 'Het :attribute moet minder bedragen dan of gelijk zijn aan :valuekilobytes.',
        'string' => 'Het :attribute moet minder bedragen dan of gelijk zijn aan :valuekarakters.',
        'array' => 'The :attribute mag niet meer dan :value items.',
    ],
    'max' => [
        'numeric' => 'The :max :attribute mag niet groter zijn dan.',
        'file' => 'The :max kilobytes :attribute mag niet groter zijn dan.',
        'string' => 'The :max karakters :attribute mag niet groter zijn dan.',
        'array' => 'The :max items :attribute mag niet meer dan te hebben.',
    ],
    'mimes' => 'The :attribute moet een bestand van het type te zijn: :values.',
    'mimetypes' => 'The :attribute moet een bestand van het type te zijn: :values.',
    'min' => [
        'numeric' => 'De :min :attribute moet minimaal zijn.',
        'file' => 'De :attribute moet minimaal :min kilobytes.',
        'string' => 'De :attribute moet minimaal :min karakters.',
        'array' => 'The :min items :attribute moeten ten minste beschikken.',
    ],
    'not_in' => 'De geselecteerde :attribute is ongeldig.',
    'not_regex' => 'The :attribute is ongeldig.',
    'numeric' => 'The :attribute moet een getal zijn.',
    'password' => 'Het wachtwoord is niet correct.',
    'present' => 'The :attribute veld moet aanwezig zijn.',
    'regex' => 'The :attribute is ongeldig.',
    'required' => 'The :attribute kenmerk is vereist.',
    'required_if' => 'The :attribute veld is vereist wanneer :other is: waarde.',
    'required_unless' => 'The :attribute kenmerk is vereist, tenzij :other in :values.',
    'required_with' => 'Het :attribute veld is vereist wanneer :values aanwezig.',
    'required_with_all' => 'Het :attribute veld is vereist wanneer :values aanwezig zijn.',
    'required_without' => 'Het :attribute veld is vereist wanneer :values niet aanwezig.',
    'required_without_all' => 'The :attribute veld is vereist wanneer geen van :values aanwezig zijn.',
    'same' => 'The :attribute en :other must match.',
    'size' => [
        'numeric' => 'The :attribute moet zijn :size.',
        'file' => 'The :attribute moet zijn :size kilobytes.',
        'string' => 'The :attribute moet zijn :size tekens.',
        'array' => 'The :attribute moet bevatten :size items.',
    ],
    'starts_with' => 'The :attribute moet beginnen met een van de volgende: :values.',
    'string' => 'The :attribute moet een tekenreeks zijn.',
    'timezone' => 'The :attribute moet een geldige zone.',
    'unique' => 'The :attribute is al genomen.',
    'uploaded' => 'The :attribute niet te uploaden.',
    'url' => 'The :attribute is ongeldig.',
    'uuid' => 'The :attribute moet een geldige UUID zijn.',

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
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

    'email-verified' => 'Het e-mailadres wordt gecontroleerd.',
    'phone-number-verified' => 'Het opgegeven telefoonnummer is geverifieerd.',
    'invalid-token' => 'De verificatie token is ongeldig of verlopen.',
    'something-went-wrong' => 'Er is iets mis gegaan, probeer het alstublieft nogmaals.'
];
