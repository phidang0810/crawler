<?php

namespace App\Services;


use App\Libraries\Shipment\Convey;
use App\Libraries\Shipment\FC;
use App\Libraries\Shipment\Manna;
use App\Libraries\Shipment\Priority;

class ShippingQuote
{
    protected $input = [];
    public function __construct($request)
    {
        $this->input = config('crawler.default_params');
        if(array_key_exists('order_number', $request)) $this->input['order_number'] = $request['order_number'];
        if(array_key_exists('shipping_zip_code', $request)) $this->input['shipping_zip_code'] = $request['shipping_zip_code'];
        if(array_key_exists('shipping_address_type', $request)) $this->input['shipping_address_type'] = $request['shipping_address_type'];
        if(array_key_exists('pickup_date', $request)) $this->input['pickup_date'] = $request['pickup_date'];
        if(array_key_exists('pallets', $request)) $this->input['pallets'] = $request['pallets'];
        if(array_key_exists('shipping_method', $request)) $this->input['service_level'] = $request['shipping_method'];
    }

    public function getQuote()
    {
        $data = [];
        $data['FC'] = $this->getFromFC();
        $data['Manna'] = $this->getFromManna();
        $data['Convey'] = $this->getFromConvey();
        return $data;
    }

    public function getFromManna()
    {
        $config = config('crawler.manna');

        $manna = new Manna($config);
        $manna->mapConfigAndInput($this->input);
        return $manna->getQuote();
    }

    public function getFromConvey()
    {
        $config = config('crawler.convey');
        $convey = new Convey($config);
        $convey->mapConfigAndInput($this->input);
        return $convey->getQuote();
    }

    public function getFromFC()
    {
        $config = config('crawler.FC');
        $fc = new FC($config);
        $fc->mapConfigAndInput($this->input, true);
        return $fc->getQuote();
    }

    public function getFromPriority()
    {
        $config = config('crawler.priority');
        $priority = new Priority($config);
        $priority->mapConfigAndInput($this->input);
        return $priority->getQuote();
    }
}