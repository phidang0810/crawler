<?php

namespace App\Libraries\Shipment;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Client;
use \Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait Platform
{
    protected $config = [];

    public function __construct($config)
    {
        $this->setConfig($config);
        $cookie = $this->getCookie();
        if (is_null($cookie)) {
           $this->_login();
        }
    }

    protected function getCookie($name = null)
    {
        if (is_null($name)) {
            $name = $this->getConfig('cookie_name');
        }
        return Cache::get($name);
    }

    protected function setConfig(array $array)
    {
        $this->config = array_merge($array, $this->config);
    }

    protected function getConfig($key)
    {
        return $this->config[$key];
    }

    public function getQuote()
    {
        return [];
    }

    protected function loginIsFail($response)
    {
        return false;
    }

    protected function setLoginName($name)
    {
        $this->login = $name;
    }

    protected function setPasswordName($name)
    {
        $this->password = $name;
    }

    protected function _login()
    {
        try {
            $jar = new CookieJar;
            $client = new Client([
                'cookies' => $jar
            ]);
            $response = $client->request('POST', $this->config['url_login'], [
                'form_params' => $this->_getAuth()
            ]);

            if ($error = $this->loginIsFail($response)) abort(Response::HTTP_UNAUTHORIZED, $error);
            $this->setConfig(['cookie' => $jar]);
            Cache::put($this->getConfig('cookie_name'), $jar, $this->getConfig('cookie_live'));
        } catch (ServerException $e) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error server');
        }
        return true;
    }

    protected function _getAuth()
    {
        $data = [
            'email' => $this->getConfig('username'),
            'password' => $this->getConfig('password')
        ];
        return $data;
    }

}

