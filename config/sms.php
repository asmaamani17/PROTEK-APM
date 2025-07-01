<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default SMS driver that is used to send any SMS
    | messages sent by your application. Alternative drivers may be setup
    | and used as needed; however, this SMS driver will be used by default.
    |
    */

    'default' => env('SMS_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | SMS Driver Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure the driver information for each service that
    | is used by your application. A default configuration has been added
    | for each driver as an example of the required options.
    |
    */

    'drivers' => [
        'log' => [
            'driver' => 'log',
        ],
        
        'twilio' => [
            'driver' => 'twilio',
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM_NUMBER'),
        ],
        
        'nexmo' => [
            'driver' => 'nexmo',
            'api_key' => env('NEXMO_KEY'),
            'api_secret' => env('NEXMO_SECRET'),
            'from' => env('NEXMO_FROM_NUMBER'),
        ],
    ],
];
