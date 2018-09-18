<?php

return [
    'FC' => [
        'username' => env('FC_USERNAME', 'support@zurifurniture.com'),
        'password' => env('FC_PASSWORD','Zuri4254#'),
        'url_login' => 'https://app.freightclub.com/Account/Login',
        'cookie_name' => 'FC.cookie',
        'cookie_live' => 1440,
        'url_generate_rate' => 'https://app.freightclub.com/QuickQuote/GenerateRates',
        'url_generate_rate_service' => 'https://app.freightclub.com/QuickQuote/GenerateRatesByServiceLevel',
        'url_add_product' => 'https://app.freightclub.com/Product/AddProductJSON',
        'url_delete_product' => 'https://app.freightclub.com/Product/Destroy',
        'default_params' => [
            'ShipmentType' => 1,
            'ProfileType' => '15', // Individual Consumer
            'PickupZip' => 75244, // Zuri
            'PickupLocation' => [
                'PostalCode' => 75244
            ],
            /*
              |--------------------------------------------------------------------------
              | Pickup/Delivery type
              |--------------------------------------------------------------------------
              |
              | 400: Residential
              | 401: Commercial
              |
            */
            'PickupType' => 401,
            'DeliveryType' => 401,
            //'NotifyBeforeDelivery' => 'true', //Delivery Appointment Required
            /*
              |--------------------------------------------------------------------------
              | Service Level
              |--------------------------------------------------------------------------
              |
              | 351: Back Of Truck
              | 352: Curbside
              | 353: Threshold
              | 354: Room of Choice
              | 355: White Glove - Packaging Removal
              | 357: White Glove - Light Assembly
              |
            */
            'ServiceLevel' => 351,
            'ShipmentItems' => []
        ]
    ],
    'convey' => [
        'username' => env('CONVEY_USERNAME', 'wrenn@zurifurniture.com'),
        'password' => env('CONVEY_PASSWORD', 'Ferg4325!'),
        'url_login' => 'https://app.getconvey.com/login',
        'url_submit' => 'https://app.getconvey.com/shipment/step-1',
        'cookie_name' => 'convey.cookie',
        'cookie_live' => 1440,
        'default_params' =>
            [
                'shipper_role' => 'SHIPPER',
                'origin_location' => '2889',
                /*
                  |--------------------------------------------------------------------------
                  | Service Level
                  |--------------------------------------------------------------------------
                  |
                  | STANDARD: Curbside/Loading Dock
                  | THRESHOLD: Threshold
                  | ROOM_OF_CHOICE: Room of choice
                  | LIGHT_ASSEMBLY: Light Assembly
                  | HEAVY_ASSEMBLY: Heavy Assembly
                  |
                */
                'handling_service_level' => 'STANDARD',

                /*
                  |--------------------------------------------------------------------------
                  | Address Type
                  |--------------------------------------------------------------------------
                  |
                  | BUSINESS: Business
                  | RESIDENTIAL: Residential
                  |
                */
                'destination_address_type' => 'BUSINESS',

                'origin_ready_time' => '8:00 AM',
                'origin_dock_close_time' => '5:00 PM'
            ]
    ],
    'manna' => [
        'username' => env('MANNA_USERNAME', 'MSPHDZFD'),
        'password' => env('MANNA_PASSWORD', '7884frisco'),
        'url_login' => 'http://go2clarity.com/login',
        'url_get_trans' => 'http://go2clarity.com/orders/saveBol',
        'url_save_pieces' => 'http://go2clarity.com/orders/savePieces',
        'url_save_extra' => 'http://go2clarity.com/orders/saveExtraServices',
        'url_get_quote' => 'http://go2clarity.com/orders/saveCharges',
        'cookie_name' => 'manna.cookie',
        'cookie_live' => 40,
        'default_params' => [
            'inCtrlID' => '17480',
            'inBillID' => '17480',
            'shipperID' => 'ZuriFurnit-6',

            /*
            |--------------------------------------------------------------------------
            | Consignee type
            |--------------------------------------------------------------------------
            |
            | 1: Hospital
            | 2: Business
            | 3: Residential
            | 4: Convention Ctr
            | 5: Hotel
            | 6: Military Base
            |
            */
            'consigneeType' => 2,
            'products' => [],

            /*
           |--------------------------------------------------------------------------
           | Shipment type
           |--------------------------------------------------------------------------
           |
           | 12: Threshold Plus
           | 35: Threshold Plus ( 2-Person)
           | 2: White Glove
           | 6: White Glove Assembly
           |
           */
            'shipType' => '2',
            //'axChk' => 'on',// Advanced Exchange
            //'axRet' => 'on', //Return Shipment
            'miscSpecInst' => '',
            'serviceType' => '31' //Ground
        ]
    ],
    'priority' => [
        'username' => env('PRIORITY1_USERNAME', 'zuri'),
        'password' => env('PRIORITY1_PASSWORD', 'midsouth1'),
        'url_login' => 'https://priority1inc.mercurygate.net/MercuryGate/login/LoginProcess.jsp',
        'url_submit' => 'https://priority1inc.mercurygate.net/MercuryGate/quote/quickQuoteStep2.jsp',
        'cookie_name' => 'priority.cookie',
        'cookie_live' => 40,
        'default_params' => [
            'originOid' => '29087422115',
            'enableMetrics' => 'false',
            'isShipmentMode' => 'true',
            'mgConfigGroupId' => 'QuoteV2',
            'originPostal' => '75244',
            'originCity' => 'DALLAS',
            'originState' => 'TX',
            'originCountry' => 'USA',
            'oLocTypeRadios' => 'business',

            /*
            |--------------------------------------------------------------------------
            | Shipping address type
            |--------------------------------------------------------------------------
            |
            | business: Business
            | residence: Residential
            |
            */
            'dLocTypeRadios' => 2
        ]
    ],

    'default_params' => [
        'shipping_zip_code' => 77006,
        'pickup_date' => '03/12/2018',
        'order_number' => '12323',
        'pallets' => [
            [
                'length' => '84',
                'width' => '36',
                'height' => '79',
                'weight' => '353',
                'dec_value' => 1804
            ]
        ]
    ],
    'format_date' => 'm/d/Y'

];
