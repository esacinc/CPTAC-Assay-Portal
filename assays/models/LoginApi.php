<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 10/12/18
 * Time: 9:39 PM
 */

namespace assays\models;


class LoginApi {

    function __construct($labkey_config) {
        $this->labkey_config = $labkey_config;
    }

    public function getCookies() {
        $url = $this->labkey_config["server_raw"] . $this->labkey_config['login_url'];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $parameters = [
            "email" => $this->labkey_config['email'],
            "password" => $this->labkey_config['password'],
            "remember" => "on"
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));

        $ch = LabkeyApi::setCurlOpt($ch);
        $auth_result = curl_exec($ch);

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $auth_result, $matches);
        $cookies = array();
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }

        curl_close($ch);
        return $cookies;
    }

    public function getGuzzleClient() {
        $url = $this->labkey_config["server_raw"] . $this->labkey_config['login_url'];

        $client = new \GuzzleHttp\Client([
            'cookies' => true,
            'verify'  => false

        ]);

        $parameters = [
            "email" => $this->labkey_config['email'],
            "password" => $this->labkey_config['password'],
            "remember" => "on"
        ];

        $client->post($url, [
            'form_params' => $parameters
        ]);

        return $client;
    }



    public function getCookiesAsString($cookies = []) {
        $cookie_str = "";
        foreach ($cookies as $key=>$value) {
            if (empty($cookie_str)) {
                $cookie_str = $key . "=" . $value;
            } else {
                $cookie_str = $cookie_str . ";" . $key . "=" . $value;
            }
        }
        return $cookie_str;
    }

}