<?php
namespace satuh;
class httpBuilder{


    protected $curlHandle;
    protected $asArray = true;
    protected $asJson;
    public $headers;
    public $httpCode;

    public function __construct()
    {
        $this->curlHandle = curl_init();
    }

    private function setCurl(){
        curl_setopt( $this->curlHandle, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt( $this->curlHandle, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
    }

    public function setHeaders($headers = array()){
        $this->headers = $headers;
    }

    public function asArray(){
        $this->asArray = true;
        $this->asJson = false;
    }

    public function asJson(){
        $this->asArray = false;
        $this->asJson = true;
    }

    public function get($uri){

        $this->setCurl();
        curl_setopt( $this->curlHandle, CURLOPT_HTTPGET, 1 );
        curl_setopt($this->curlHandle,CURLOPT_URL,$uri);
        return $this->returnResponse();
    }

    public function post($uri,$array_post)
    {
        $this->setCurl();
        curl_setopt( $this->curlHandle, CURLOPT_POST, 1 );
        curl_setopt($this->curlHandle,CURLOPT_URL,$uri);
        curl_setopt($this->curlHandle,CURLOPT_POSTFIELDS, $array_post);
        return $this->returnResponse();
    }

    protected function returnResponse(){
        if($this->asJson){
            $res =  curl_exec($this->curlHandle);
            $this->httpCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
            return $res;
        }

        $res =  json_decode(curl_exec($this->curlHandle),true);
        $this->httpCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        return $res;
    }

    public function getHttpCode(){
        return $this->httpCode;
    }


}