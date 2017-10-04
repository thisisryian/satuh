<?php

class httpBuilder{


    protected $curlHandle;
    public $headers = array(
        'Authorization: ',
        'Accept: application/json'

    );
    function __construct($access_token = null)
    {
        if(!empty($access_token)){
            $this->headers[0] .= "Bearer ".urlencode($access_token);
        }
        curl_setopt( $this->curlHandle, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt( $this->curlHandle, CURLOPT_RETURNTRANSFER, true );
        $this->curlHandle = curl_init();
    }

    public function get($uri){

        curl_setopt( $this->curlHandle, CURLOPT_HTTPGET, 1 );
        curl_setopt($this->curlHandle,CURLOPT_URL,$uri);
        curl_exec($this->curlHandle);
        $response = json_decode(curl_exec($this->curlHandle));
        return $response;
    }


    protected function post($uri,$array_post)
    {
        curl_setopt( $this->curlHandle, CURLOPT_POST, 1 );
        curl_setopt($this->curlHandle,CURLOPT_URL,$uri);
        curl_setopt($this->curlHandle,CURLOPT_POSTFIELDS, $array_post);
        $response = json_decode(curl_exec($this->curlHandle));
        return $response;
    }

}