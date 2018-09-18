<?php

namespace App\Libraries\Shipment;

use App\Libraries\BusinessDay;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\Response;
use Symfony\Component\DomCrawler\Crawler;

class FC
{
    use Platform;

    protected $parameter = [];
    protected $productsNeedDelete = [];
    const SHIPPING_ADDRESS_TYPE = [
        ADR_TYPE_BUSINESS => 401,
        ADR_TYPE_RESIDENTIAL => 400
    ];

    const SERVICE_LEVEL = [
        SERVICE_LEVEL_DOCK_TO_DOCK => 351,
        SERVICE_LEVEL_CURBSIDE => 352,
        SERVICE_LEVEL_ROOM_OF_CHOOSE => 354,
        SERVICE_LEVEL_WHITE_GLOVE => 357
    ];

    public function _getAuth()
    {
        $data = [
            'Email' => $this->getConfig('username'),
            'Password' => $this->getConfig('password'),
            '__RequestVerificationToken' => $this->_getToken()
        ];
        return $data;
    }

    private function _getToken()
    {
        $client = new Client();
        $response = $client->request('GET', $this->getConfig('url_login'));
        $body = $response->getBody();
        $html = (string)$body;
        $crawler = new Crawler($html);
        $token = $crawler->filter('input[name=__RequestVerificationToken]')->attr('value');
        return $token;
    }

    protected function loginIsFail($response)
    {
        $body = $response->getBody();
        $html = (string)$body;
        $crawler = new Crawler($html);
        $errorSumarry = $crawler->evaluate('count(//div[@class="validation-summary-errors text-danger"])');
        $errorValid = $crawler->evaluate('count(//span[@class="field-validation-error text-danger"])');
        if ($errorSumarry[0] >= 1 || $errorValid[0] >= 1) return '[FC] Email or password is invalid';
        return false;
    }

    public function getQuote()
    {
        $cookie = $this->getCookie();
        $client = new Client(['cookies' => $cookie]);
        $params = $this->parameter;
        $serviceLevel = $params['ServiceLevel'];
        $orderInfo = $this->_getOrderInfo($params);
        if (!$orderInfo['status']) {
            return [
                'error' => $orderInfo['errors'],
                'success' => false
            ];
        }

        $params['OrderID'] = $orderInfo['order_id'];
        $params['Rate'] = $orderInfo['rate'];
        //get Quote

        for ($i = 0; $i < 5; $i++) {
            $response = $client->request('POST', $this->getConfig('url_generate_rate_service'), [
                'headers' => [
                    'referer' => 'https://app.freightclub.com/Home/QuickQuote',
                ],
                'form_params' => $params
            ]);
            sleep(1);
        }
        $quotes = json_decode($response->getBody(), true);
        $quotes = $quotes['rates']['Quotes'] ?? [];
        $result = [];
        foreach ($quotes as $quote) {
            if ($quote['ServiceLevel'] != $serviceLevel) continue;

            $transitDays = (int)preg_replace('/[^0-9]/', '', $quote['TransitTime']);
            $price = round($quote['NetCharge'], 2);
            $result[] = [
                'carrier_name' => $quote['CarrierName'],
                'carrier_code' => $quote['CarrierScacCode'],
                'transit_days' => empty($transitDays) ? null : $transitDays,
                'est_delivery' => empty($transitDays) ? null : BusinessDay::add($params['org_pickup_date'], $transitDays),
                'quote' => $price,
                'service_level' => [$quote['ServiceLevelDescription']],
                'shipping_method' => SERVICE_LEVEL[$params['m_service_level']],
                'price' => $price
            ];
        }
        usort($result, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });

        $this->deleteProduct($this->productsNeedDelete);
        return [
            'success' => true,
            'quotes' => $result
        ];
    }

    private function deleteProduct($products)
    {
        $client = new Client(['cookies' => $this->getCookie()]);
        foreach ($products as $product) {
            $client->request('POST', $this->getConfig('url_delete_product'), [
                'form_params' => $product
            ]);
        }
    }

    private function _getOrderInfo($params)
    {
        $client = new Client(['cookies' => $this->getCookie()]);
        $response = $client->request('POST', $this->getConfig('url_generate_rate'), [
            'headers' => [
                'referer' => 'https://app.freightclub.com/Home/QuickQuote'
            ],
            'form_params' => $params
        ]);
        $result = json_decode($response->getBody(), true);
        $errors = [];
        if (key_exists('errors', $result)) {
            $errors = $result['errors'];
        }
        if (!isset($result['rates']) || !isset($result['rates']['OrderID'])) {
            return [
                'errors' => $errors,
                'status' => false
            ];
        }

        return [
            'status' => true,
            'order_id' => $result['rates']['OrderID'],
            'rate' => $result['rates']['SelectedServiceLevelQuote']['NetCharge']
        ];
    }

    public function mapConfigAndInput($input, $addProduct = false)
    {
        $result = config('crawler.FC.default_params');
        $client = new Client(['cookies' => $this->getCookie()]);
        foreach ($input['pallets'] as $key => $item) {
            $uniqueKey = microtime();
            $productInfo = [
                'Name' => $item['name'] ?? "product $uniqueKey",
                'Price' => $item['dec_value'],
                'CartonItems' => [
                    [
                        'length' => $item['length'],
                        'width' => $item['width'],
                        'height' => $item['height'],
                        'weight' => $item['weight'],
                        'category' => 347, // other
                        'freightclass' => $item['freight_class']
                    ]
                ]
            ];

            try {
                $response = $client->request('POST', $this->getConfig('url_add_product'), [
                    'headers' => [
                        'referer' => 'https://app.freightclub.com/Home/QuickQuote'
                    ],
                    'form_params' => $productInfo
                ]);
            } catch (ServerException $e) {
                abort(Response::HTTP_BAD_REQUEST, 'Internal Server Error');
            }

            $productRes = json_decode($response->getBody(), true);

            if (!is_null($productRes['Message']) && empty($productRes['ProductID'])) {
                abort(Response::HTTP_BAD_REQUEST, $productRes['Message']);
            }

            $this->productsNeedDelete[] = [
                'ProductID' => $productRes['ProductID'],
                'Sku' => $productRes['SKU'],
                'Name' => $productRes['Name']
            ];

            for ($i = 1; $i <= $item['num_of_pallet']; $i++) {
                $result['ShipmentItems'][] = [
                    'PackagingType' => 117, // add new product
                    'LTLParclePackageType' => $productRes['ProductID'], //custom pallet
                    'CartonCount' => 1,
                    'Quantity' => 1,
                    'Cartons' => [
                        [
                            'Weight' => $item['weight'],
                            'Height' => $item['height'],
                            'Width' => $item['width'],
                            'Length' => $item['length'],
                            'Quantity' => 1,
                            'Category' => 347, // other
                            'FreightClass' => $item['freight_class'],
                            'SuggestedFreightClass' => $item['freight_class']
                        ]
                    ],
                    'ProductCost' => $item['dec_value'],
                    'ProductID' => $productRes['ProductID'],
                    'Weight' => '0',
                    'Length' => '0',
                    'Width' => '0',
                    'Height' => '0',
                    'ShippingClass' => '',
                    'SuggestedShippingClass' => '0',
                    'IsAttached' => 'false',
                    'NoDims' => 'false',
                    'SKU' => $productRes['SKU'],
                    'Name' => $productRes['Name'],
                ];
            }
        }
        $result['DropoffZip'] = $input['shipping_zip_code'];
        $result['DropoffLocation']['PostalCode'] = $input['shipping_zip_code'];
        $result['PickupDate'] = Carbon::createFromFormat(config('crawler.format_date'), $input['pickup_date'])->format('Y-m-d');
        $result['org_pickup_date'] = $input['pickup_date'];
        $result['OrderReferenceID'] = $input['order_number'];
        $result['DeliveryType'] = self::SHIPPING_ADDRESS_TYPE[$input['shipping_address_type']];
        $result['ServiceLevel'] = self::SERVICE_LEVEL[$input['service_level']];
        $result['m_service_level'] = $input['service_level'];
        return $this->parameter = $result;
    }
}

