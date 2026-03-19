<?php

return [
    'required' => 'El campo :attribute es obligatorio.',

    'string' => 'El campo :attribute debe ser una cadena de caracteres.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'min' => [
        'string' => 'El campo :attribute debe contener al menos :min caracteres.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
    ],
    'max' => [
        'string' => 'El campo :attribute no debe exceder de :max caracteres.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
    ],

    'unique' => 'El valor de :attribute ya está registrado.',

    'attributes' => [
        'concepto' => 'concepto',
        'monto' => 'monto',
        'curp' => 'CURP',
        'email' => 'correo electrónico',
    ],
];

