<?php

namespace App\Libraries\Shipment;

use App\Libraries\Helper;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler as Cr;
use GuzzleHttp\Client;

class Convey
{
    use Platform;

    const CARRIER_NAME = [0, 'Carrier'];
    const TRANSIT_DAY = [1, 'transit_days'];
    const EST_DELIVERY = [2, 'est_delivery'];
    const NET_CHARGE = [3, 'quote'];

    const SHIPPING_ADDRESS_TYPE = [
        ADR_TYPE_BUSINESS => 'BUSINESS',
        ADR_TYPE_RESIDENTIAL => 'RESIDENTIAL'
    ];

    const SERVICE_LEVEL = [
        SERVICE_LEVEL_DOCK_TO_DOCK => 'STANDARD',
        SERVICE_LEVEL_CURBSIDE => 'STANDARD'
    ];

    protected $parameter = [];

    public function getQuote()
    {
        $result = [
          'success' => true,
          'quotes' => []
        ];
        $params = $this->parameter;
        if (is_null($params['handling_service_level'])) return $result;

        $client = new Client(['cookies' => $this->getCookie()]);
        $response = $client->request('POST', $this->getConfig('url_submit'), [
            'form_params' => $params
        ]);
        $body = $response->getBody();
        $html = (string)$body;
        $crawler = new Cr($html);
        $crawler->filter('table#rates-table')->html();
        $result['quotes'] = $crawler->filter('table#rates-table > tbody > tr')->each(function ($tr, $i) use (&$carries, $params) {
            $column = [];
            $tr->filter('td')->each(function ($td, $i) use (&$column) {
                $data = [];
                switch ($i) {
                    case self::CARRIER_NAME[0]:
                        $img = $td->filter('img');
                        $data['carrier_name'] = $img->attr('alt');
                        $data['carrier_code'] = $img->attr('alt');
                        break;

                    case self:: TRANSIT_DAY[0]:
                        $data[self::TRANSIT_DAY[1]] = Helper::convertToNumber(trim($td->text()));
                        break;

                    case self::NET_CHARGE[0]:
                        $data[self::NET_CHARGE[1]] = Helper::convertToNumber(trim($td->text()));
                        break;

                    case self::EST_DELIVERY[0]:
                        $timestamp = Carbon::createFromFormat('D m/d/y', trim($td->text()))->format(config('crawler.format_date'));
                        $data[self::EST_DELIVERY[1]] = $timestamp;
                        break;

                    default:

                }
                $column = array_merge($column, $data);
            });
            $column['service_level'] = $this->_getServiceLevelName($params);
            $column['shipping_method'] = SERVICE_LEVEL[$params['m_service_level']];
            return $column;
        });
        return $result;
    }

    protected function loginIsFail($response)
    {
        $body = $response->getBody();
        $html = (string)$body;
        $crawler = new Cr($html);
        $result = $crawler->evaluate('count(//div[@class="alert alert-danger"])');
        if ($result[0] == 1) return '[Convey] Email or password is invalid';
        return false;
    }

    public function mapConfigAndInput($input)
    {
        $result = config('crawler.convey.default_params');
        foreach ($input['pallets'] as $key => $item) {
            for ($i = 1; $i <= $item['num_of_pallet']; $i++) {
                $result['name'][] = $item['name'] ?? "pallets $key";
                $result['weight'][] = $item['weight'];
                $result['length'][] = $item['length'];
                $result['width'][] = $item['width'];
                $result['height'][] = $item['height'];
                $result['packaging'][] = 'PALLET';
                $result['handling_units'][] = 1;
                $result['pieces'][] = $item['num_of_carton'];
                $result['freight_class'][] = 'CLASS_' . $item['freight_class'];
                $result['nmfc_primecode'][] = '';
                $result['nmfc_subcode'][] = '';
            }
        }
        $result['destination_postal_code'] = $input['shipping_zip_code'];
        $result['pickup_date'] = Carbon::createFromFormat(config('crawler.format_date'), $input['pickup_date'])->format('F d, Y');
        $result['ref_number'] = $input['order_number'];
        $result['destination_address_type'] = self::SHIPPING_ADDRESS_TYPE[$input['shipping_address_type']];
        $result['handling_service_level'] = self::SERVICE_LEVEL[$input['service_level']] ?? null;
        $result['m_service_level'] = $input['service_level'];

        if ($input['service_level'] == SERVICE_LEVEL_CURBSIDE) {
            $result['destination_accessorials[]'] = 'LIFTGATE';
        }

        return $this->parameter = $result;
    }

    private function _getServiceLevelName($params)
    {
        $serviceId = $params['handling_service_level'];
        $data = [];
        $extra = ' (No LiftGate)';
        if (key_exists('destination_accessorials[]', $params)) {
            $data[] = ' (LiftGate)';
        }
        switch ($serviceId) {
            case 'STANDARD':
                $data[] = 'Curbside/Loading Dock' . $extra;
                break;
            case 'THRESHOLD':
                $data[] = 'Threshold';
                break;
            case 'ROOM_OF_CHOICE':
                $data[] = 'Room of choice';
                break;
            case 'LIGHT_ASSEMBLY':
                $data[] = 'Light Assembly';
                break;
            case 'HEAVY_ASSEMBLY':
                $data[] = 'Heavy Assembly';
                break;
            default:
                break;
        }


        return $data;
    }
}

