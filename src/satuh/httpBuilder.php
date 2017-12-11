<?php
namespace satuh;
class httpBuilder{


    protected $curlHandle;
    protected $responseAs = true;
    public $headers = [];
    public $httpCode;
    public $responseHeader;

    public function __construct()
    {
        $this->curlHandle = curl_init();
    }

    private function setCurl(){

        curl_setopt( $this->curlHandle, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($this->curlHandle, CURLOPT_VERBOSE, 1);
        curl_setopt($this->curlHandle, CURLOPT_HEADER, 0);
        curl_setopt( $this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
    }

    public function setHeaders($headers = array()){
        $this->headers = $headers;
    }

    public function asJson(){
        $this->responseAs = false;
    }

    public function asArray(){
        $this->responseAs = true;
    }

    public function getResponseAs(){
        return $this->responseAs;
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

    public function put($uri,$array_post)
    {
        $this->setCurl();
        curl_setopt( $this->curlHandle, CURLOPT_PUT, 1 );
        curl_setopt($this->curlHandle,CURLOPT_URL,$uri);
        curl_setopt($this->curlHandle,CURLOPT_POSTFIELDS, $array_post);
        return $this->returnResponse();
    }

    protected function returnResponse(){
        $response = curl_exec($this->curlHandle);
        $header_size = curl_getinfo($this->curlHandle, CURLINFO_HEADER_SIZE);
        $response_header = substr($response, 0, $header_size);
        $this->responseHeader = $response_header;
        $res =  json_decode($response,$this->responseAs);
        $this->httpCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        return $res;
    }

    public function getResponseHeader(){
        return $this->responseHeader;
    }

    public function getHttpCode(){
        return $this->httpCode;
    }


}