<?php

namespace App\Libraries\Shipment;

use App\Libraries\BusinessDay;
use App\Libraries\Helper;
use Symfony\Component\DomCrawler\Crawler as Cr;
use GuzzleHttp\Client;

class Manna
{
    use Platform;

    protected $parameter = [];
    const SHIPPING_ADDRESS_TYPE = [
        ADR_TYPE_BUSINESS => 2,
        ADR_TYPE_RESIDENTIAL => 2
    ];

    const SERVICE_LEVEL = [
        SERVICE_LEVEL_CURBSIDE => 35,
        SERVICE_LEVEL_DOCK_TO_DOCK => 35,
        SERVICE_LEVEL_ROOM_OF_CHOOSE => 2,
        SERVICE_LEVEL_WHITE_GLOVE => 6
    ];

    protected function _getAuth()
    {
        $data = [
            'Username' => $this->getConfig('username'),
            'Password' => $this->getConfig('password')
        ];
        return $data;
    }

    public function getQuote()
    {
        $params = $this->parameter;
        $cookie = $this->getCookie();
        $client = new Client(['cookies' => $cookie]);

        //get tranUID
        $response = $client->request('POST', $this->getConfig('url_get_trans'), [
            'form_params' => $params
        ]);
        $result = json_decode($response->getBody(), true);
        $error = $result['detail'] ?? 'Server error';
        if (!isset($result['inTransUID'])) return ['success' => false, 'error' => $error];

        $deliveryDate = new \DateTime($result['dtTransDeliverDate']);
        $deliveryDate = $deliveryDate->format(config('crawler.format_date'));
        $params['inTransUID'] = $result['inTransUID'];

        //send request save pieces
        $client->request('POST', $this->getConfig('url_save_pieces'), [
            'form_params' => $params
        ]);

        //send request save extra
        $client->request('POST', $this->getConfig('url_save_extra'), [
            'form_params' => $params
        ]);


        // get Quote by tranUID
        $response = $client->request('POST', $this->getConfig('url_get_quote'), [
            'form_params' => [
                'inTransUID' => $result['inTransUID']
            ]
        ]);
        $result = json_decode($response->getBody(), true);
        $data = $result['data'] ?? [];
        $carries = [];
        $netCharge = 0;
        foreach ($data as $item) {
            $amount = Helper::convertToNumber($item['Amt']);
            $netCharge += $amount;
            $carries[] = [
                'Name' => $item['Desc'],
                'Value' => Helper::convertToNumber($item['Value']),
                'Rate' => Helper::convertToNumber($item['Rate']),
                'Amount' => $amount,
                'Type' => $item['Type'],
                'Factor Type' => $item['FT'],
            ];
        }
        $quote = [
            'carrier_name' => 'Manna',
            'carrier_code' => 'Manna',
            'transit_days' => BusinessDay::getNumberBetween($params['readyDate'], $deliveryDate),
            'est_delivery' => $deliveryDate,
            'quote' => round($netCharge, 2),
            'service_level' => $this->_getServiceLevelName($params['shipType']),
            'shipping_method' => SERVICE_LEVEL[$params['m_service_level']]
        ];

        return [
            'success' => true,
            'quotes' => [$quote]
        ];
    }

    protected function loginIsFail($response)
    {
        $body = $response->getBody();
        $html = (string)$body;
        $crawler = new Cr($html);
        try {
            $error = $crawler->filter('.text-danger')->text();
            if ($error !== '') return $error;

        } catch (\Exception $e) {

        }

        return false;
    }

    public function mapConfigAndInput($input)
    {
        $result = config('crawler.manna.default_params');
        foreach ($input['pallets'] as $item) {
            for ($i = 1; $i <= $item['num_of_pallet']; $i++) {
                $result['products'][] = [
                    'prodPieces' => 1,
                    'prodCartons' => $item['num_of_carton'],
                    'newProductChk' => 'on',
                    'prodLength' => $item['length'],
                    'prodWidth' => $item['width'],
                    'prodHeight' => $item['height'],
                    'prodWeight' => $item['weight'],
                    'prodDecVal' => $item['dec_value'],
                    //'prodPalletize' => 'on'
                ];
            }
        }
        $result['consigneeZip'] = $input['shipping_zip_code'];
        $result['readyDate'] = $input['pickup_date'];
        $result['miscRef'] = $input['order_number'];
        $result['consigneeType'] = self::SHIPPING_ADDRESS_TYPE[$input['shipping_address_type']];
        $result['shipType'] = self::SERVICE_LEVEL[$input['service_level']];
        $result['m_service_level'] = $input['service_level'];
        return $this->parameter = $result;
    }

    private function _getServiceLevelName($id)
    {
        $data = [];
        switch ($id) {
            case 12:
                $data[] = 'Threshold Plus';
                break;
            case 35:
                $data[] = 'Threshold Plus ( 2-Person)';
                break;
            case 2:
                $data[] = 'White Glove';
                break;
            case 6:
                $data[] = 'White Glove Assembly';
                break;
            default:
                break;
        }
        return $data;
    }
}

