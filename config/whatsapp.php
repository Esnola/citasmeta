<?php

$metaPhoneNumberId = env('META_WHATSAPP_PHONE_NUMBER_ID', env('WHATSAPP_CLOUD_API_PHONE_NUMBER_ID'));
$metaWabaId = env('META_WHATSAPP_WABA_ID', env('WHATSAPP_CLOUD_API_WABA_ID'));
$metaAccessToken = env('META_WHATSAPP_ACCESS_TOKEN', env('WHATSAPP_CLOUD_API_ACCESS_TOKEN'));
$metaAppSecret = env('META_WHATSAPP_APP_SECRET', env('WHATSAPP_CLOUD_API_APP_SECRET'));
$metaVerifyToken = env('META_WHATSAPP_VERIFY_TOKEN', env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'));
$metaTestRecipient = env('META_WHATSAPP_TEST_RECIPIENT', env('WHATSAPP_TEST_RECIPIENT'));
$metaBaseUrl = env('META_WHATSAPP_BASE_URL', env('WHATSAPP_CLOUD_API_BASE_URL', 'https://graph.facebook.com'));
$metaVersion = env('META_WHATSAPP_VERSION', env('WHATSAPP_CLOUD_API_VERSION', 'v22.0'));
$metaTimeout = env('META_WHATSAPP_TIMEOUT', env('WHATSAPP_CLOUD_API_TIMEOUT', 15));

return [
    'driver' => env('WHATSAPP_DRIVER', 'log'),

    'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '+34'),

    'message_mode' => env('WHATSAPP_MESSAGE_MODE', 'template'),

    'template_language_code' => env('WHATSAPP_TEMPLATE_LANGUAGE_CODE', 'es_ES'),

    'meta' => [
        'base_url' => $metaBaseUrl,
        'version' => $metaVersion,
        'phone_number_id' => $metaPhoneNumberId,
        'waba_id' => $metaWabaId,
        'access_token' => $metaAccessToken,
        'app_secret' => $metaAppSecret,
        'verify_token' => $metaVerifyToken,
        'test_recipient' => $metaTestRecipient,
        'timeout' => $metaTimeout,
    ],

    'cloud_api' => [
        'base_url' => $metaBaseUrl,
        'version' => $metaVersion,
        'phone_number_id' => $metaPhoneNumberId,
        'access_token' => $metaAccessToken,
        'timeout' => $metaTimeout,
    ],

    'webhook' => [
        'verify_token' => $metaVerifyToken,
        'app_secret' => $metaAppSecret,
        'waba_id' => $metaWabaId,
    ],

    'default_template' => env('WHATSAPP_DEFAULT_TEMPLATE', 'clinical_reminder'),

    'default_message' => env(
        'WHATSAPP_DEFAULT_MESSAGE',
        'Hola [NOMBRE] te recordamos que el día [DIA] tienes una cita a las [HORA] ; saludos Clínica Dental'
    ),

    'templates' => [
        'confirmar_cita' => [
            'label' => 'Confirmar cita',
            'message' => 'Hola [NOMBRE], te recordamos que el día [DIA] tienes una cita a las [HORA]. Saludos, Clínica Dental Eugenia',
        ],
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
