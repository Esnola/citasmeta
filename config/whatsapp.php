<?php

return [
    'driver' => env('WHATSAPP_DRIVER', 'log'),

    'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '+34'),

    'message_mode' => env('WHATSAPP_MESSAGE_MODE', 'text'),

    'cloud_api' => [
        'base_url' => env('WHATSAPP_CLOUD_API_BASE_URL', 'https://graph.facebook.com'),
        'version' => env('WHATSAPP_CLOUD_API_VERSION', 'v22.0'),
        'phone_number_id' => env('WHATSAPP_CLOUD_API_PHONE_NUMBER_ID'),
        'access_token' => env('WHATSAPP_CLOUD_API_ACCESS_TOKEN'),
        'timeout' => env('WHATSAPP_CLOUD_API_TIMEOUT', 15),
    ],

    'default_template' => env('WHATSAPP_DEFAULT_TEMPLATE', 'clinical_reminder'),

    'default_message' => env(
        'WHATSAPP_DEFAULT_MESSAGE',
        'Hola [NOMBRE] te recordamos que el día [DIA] tienes una cita a las [HORA] ; saludos Clínica Dental'
    ),

    'templates' => [
        'clinical_reminder' => [
            'label' => 'Recordatorio clínica',
            'message' => env('WHATSAPP_DEFAULT_MESSAGE'),
        ],
        'formal_reminder' => [
            'label' => 'Recordatorio formal',
            'message' => 'Estimado/a [NOMBRE] [APELLIDOS], le recordamos su cita el [DIA] a las [HORA]. Saludos, Clínica Dental ',
        ],
        'short_reminder' => [
            'label' => 'Recordatorio breve',
            'message' => 'Hola [NOMBRE], recuerde su cita el [DIA] a las [HORA]. Tel: [TELEFONO]',
        ],
    ],
];
