<?php

namespace App\Services;


use Maatwebsite\Excel\Facades\Excel;

class QuoteExcel
{
    protected $data;
    protected $dataExport;
    public function __construct($data)
    {
        $this->data = $data;
        $this->_mappingData($data);
    }

    public function export()
    {
        $data = $this->data;
        $dataExport = $this->dataExport;
        $name = 'Order#' . $data['general']['order_number'] . '-' . date('m-d-Y');
        Excel::create($name, function($excel) use($data, $dataExport) {
            $excel->sheet('Quotes', function($sheet) use($data, $dataExport) {
                $sheet->setAutoSize(true);
                $sheet->setFontSize(14);

                $sheet->setColumnFormat([
                    'E' => '0',
                    'F' => 'mm/dd/yyyy',
                    'G' => '"$"#,##0.00_-'
                ]);

                $sheet->cell('A2', function ($cell) {
                    $cell->setFontWeight('bold');
                });

                $sheet->cells('A7:F7', function ($cells) {
                    $cells->setFontWeight('bold');
                });

                $rowQuoteTitle = count($data['general']['pallets']) + 9;
                $sheet->cells("A$rowQuoteTitle:G$rowQuoteTitle", function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#e9ecef');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $sheet->setHeight($rowQuoteTitle, 30);

                $sheet->fromArray($dataExport);

            });

        })->download('xlsx');
    }

    private function _mappingData($data)
    {
        $genaral = $data['general'];
        $result = [
            ['General Information'],
            ['Order#', $genaral['order_number']],
            ['Pickup Date', $genaral['pickup_date']],
            ['Shipping To', $genaral['shipping_zip_code'] . ' ( '. $genaral['shipping_address_type'] .')'],
            [],
            ['Pallet#', 'Carton#', 'Length x Width x Height', 'Weight', 'Declared Value', 'Freight Class']
        ];

        foreach ($genaral['pallets'] as $item) {
            $result[] = [
                $item['num_of_pallet'],
                $item['num_of_carton'],
                $item['length'] . ' x ' . $item['width'] . ' x ' . $item['height'],
                $item['weight'],
                $item['dec_value'],
                $item['freight_class'],
            ];
        }

        $result[] = [];
        $result[] = [
            'Order#',
            'Shipping Method',
            'Portal',
            'Carrier',
            'Transit Days',
            'Delivery Date',
            'Quote'
        ];

        foreach ($data['convey'] as $item) {
            $result[] = [
                $genaral['order_number'],
                $item['shipping_method'],
                'Convey',
                $item['carrier_name'],
                (int)$item['transit_days'],
                $item['est_delivery'],
                (float)$item['quote'],
            ];
        }

        foreach ($data['manna'] as $item) {
            $result[] = [
                $genaral['order_number'],
                $item['shipping_method'],
                'Manna',
                $item['carrier_name'],
                (int)$item['transit_days'],
                $item['est_delivery'],
                (float)$item['quote'],
            ];
        }

        foreach ($data['fc'] as $item) {
            $result[] = [
                $genaral['order_number'],
                $item['shipping_method'],
                'FC',
                $item['carrier_name'],
                (int)$item['transit_days'],
                $item['est_delivery'],
                (float)$item['quote'],
            ];
        }

        foreach ($data['priority'] as $item) {
            $result[] = [
                $genaral['order_number'],
                $item['shipping_method'],
                'Priority 1',
                $item['carrier_name'],
                (int)$item['transit_days'],
                $item['est_delivery'],
                (float)$item['quote'],
            ];
        }

        return $this->dataExport = $result;
    }
}