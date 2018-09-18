<?php

namespace App\Libraries\Shipment;

use Symfony\Component\DomCrawler\Crawler as Cr;
use GuzzleHttp\Client;

class Priority
{
    use Platform;

    const SHIPPING_ADDRESS_TYPE = [
        ADR_TYPE_BUSINESS => 'business',
        ADR_TYPE_RESIDENTIAL => 'residence'
    ];

    //Don't have service level
    const SERVICE_LEVEL = [
        SERVICE_LEVEL_DOCK_TO_DOCK => SERVICE_LEVEL_DOCK_TO_DOCK,
        SERVICE_LEVEL_CURBSIDE => SERVICE_LEVEL_CURBSIDE
    ];

    protected $parameter = [];

    protected function _getAuth()
    {
        $data = [
            'UserId' => $this->getConfig('username'),
            'Password' => $this->getConfig('password')
        ];
        return $data;
    }

    public function getQuote()
    {
        $result = [
          'success' => true,
          'quotes' => []
        ];
        $params = $this->parameter;
        if (is_null($params['handling_service_level'])) return $result;

        $client = new Client(['cookies' => $this->getCookie()]);
        $response = $client->request('POST', 'https://priority1inc.mercurygate.net/MercuryGate/quote/quickQuoteStep2.jsp', [
            'form_params' => $params
        ]);
        $body = $response->getBody();
        $html = (string)$body;
        preg_match_all('/\<div class\=\"carrierName strong1\"\>(.*?)\<\/div\>/', $html, $carrierNameArr);
        preg_match_all('/\<div class\=\"transitLine1\"\>(.*?).0 days\<\/div\>/', $html, $transitDayArr);
        preg_match_all('/Estimated delivery\<\/span\> \<span\>(.*?)\*\<\/span\>/', $html, $deliveryArr);
        preg_match_all('/rateTotalLine1\"\>\$(.*?) USD\<\/div\>/', $html, $quoteArr);
        $quotes = [];
        foreach ($carrierNameArr[1] as $key => $name) {
            $quotes[] = [
                'carrier_name' => $name,
                'carrier_code' => $name,
                'transit_days' => $transitDayArr[1][$key] ?? null,
                'est_delivery' => $deliveryArr[1][$key] ?? null,
                'quote' => $quoteArr[1][$key] ?? null,
                'service_level' => $this->_getServiceLevelName($params),
                'shipping_method' => SERVICE_LEVEL[$params['m_service_level']]
            ];
        }
        return [
            'success' => true,
            'quotes' => $quotes
        ];
    }

    protected function loginIsFail($response)
    {
        $body = $response->getBody();
        $html = (string)$body;
        $crawler = new Cr($html);
        $titlePage = $crawler->filterXPath('//title')->text();
        if (trim($titlePage) === 'TMS Login') return '[Priority1] Email or password is invalid';
        return false;
    }

    public function mapConfigAndInput($input)
    {
        $result = config('crawler.priority.default_params');
        $index = 1;
        foreach ($input['pallets'] as $item) {
            for ($i = 1; $i <= $item['num_of_pallet']; $i++) {
                $result["itemOid$index"] = 0;
                $result["itemQty$index"] = 1;
                $result["itemQtyUom$index"] = 'PLT';
                $result["itemWeight$index"] = $item['weight'];
                $result["itemWeightUom$index"] = 'lb';
                $result["itemWeightType$index"] = 'total';
                $result["itemLenUom$index"] = 'in';
                $result["itemDesc$index"] = "des $index";
                $result["itemLen$index"] = $item['length'];
                $result["itemWid$index"] = $item['width'];
                $result["itemHgt$index"] = $item['height'];
                $result["itemFClass$index"] = $item['freight_class'];
                $index++;
            }
        }
        $index--;
        $result['lastItem'] = $index;
        $result['destPostal'] = $input['shipping_zip_code'];
        $result['pickupDate'] = $input['pickup_date'];
        $result['dLocTypeRadios'] = self::SHIPPING_ADDRESS_TYPE[$input['shipping_address_type']];
        $result['handling_service_level'] = self::SERVICE_LEVEL[$input['service_level']] ?? null;
        $result['m_service_level'] = $input['service_level'];

        if ($input['service_level'] === SERVICE_LEVEL_CURBSIDE) {
            $result['serviceLGD'] = 'on';
        }

        if ($input['shipping_address_type'] === ADR_TYPE_RESIDENTIAL && $input['service_level'] !== SERVICE_LEVEL_DOCK_TO_DOCK) {
            $result['serviceRD'] = 'on';
        }

        return $this->parameter = $result;
    }

    private function _getServiceLevelName($params)
    {
        $data = [];
        if (key_exists('serviceLGD', $params)) $data[] = 'Lift Gate Delivery';
        if (key_exists('serviceRD', $params)) $data[] = 'Residential Delivery';
        return $data;
    }
}

