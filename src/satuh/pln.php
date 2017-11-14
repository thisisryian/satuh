<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 07/11/2017
 * Time: 10.04
 */

namespace satuh;
use satuh\httpBuilder;
use InvalidArgumentException;
class pln
{
    const URL = "https://epay.satuh.com/api/pln";
    protected $accessToken = null;
    protected $clientId;
    protected $clientSecret;
    protected $httpBuilder;
    protected $ENVIROMENT;
    protected $defaultHeaders = array(
        'Authorization: ',
        'Accept: application/json'
    );

    public function __construct($client_id,$client_secret,$environment)
    {
        if (empty($client_id)) throw new InvalidArgumentException("Client Id is not specified");
        if (empty($client_secret)) throw new InvalidArgumentException("Client Secret is not specified");
        if (empty($environment)) throw new InvalidArgumentException("Please set your enviroment");

        $this->setEnviroment($environment);
        $this->httpBuilder = new httpBuilder();
        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
        $this->curlHandle = curl_init();
        $this->authorization();
    }

    private function authorization(){
        $satuh = new auth($this->clientId,$this->clientSecret);
        $response = $satuh->getAccessToken('client');
        if(isset($response['access_token'])){
            $this->accessToken = $response['access_token'];
            $this->defaultHeaders[0] .= "Bearer ".urlencode($this->accessToken);
        }
        $this->httpBuilder->setHeaders($this->defaultHeaders);
    }

    public function setEnviroment($environment){

        if($environment !== "production" || $environment !== "testing") throw new InvalidArgumentException("Environment could only be set to production or testing");
        $this->ENVIROMENT = $environment;
        $this->defaultHeaders[2] .= $this->ENVIROMENT;
    }

    function asArray(){
        $this->httpBuilder->asArray();
    }

    function asJson(){
        $this->httpBuilder->asJson();
    }

    function getPlnPriceList($product_code,$pln_id){
        if (empty($product_code)) throw new InvalidArgumentException("Product Code is not specified");
        if (empty($pln_id)) throw new InvalidArgumentException("PLN Id is not specified");
        return $this->httpBuilder->post(self::URL,['product_code'=>$product_code,'pln_id' => $pln_id]);
    }

    function payment($product_code,$pln_id,$nominal){
        if (empty($product_code)) throw new InvalidArgumentException("Product Code is not specified");
        if (empty($pln_id)) throw new InvalidArgumentException("PLN Id is not specified");
        if (empty($nominal)) throw new InvalidArgumentException("Nominal is not specified");
        return $this->httpBuilder->post(self::URL.'/payment',['product_code'=>$product_code,'pln_id' => $pln_id,'nominal' => $nominal]);
    }


    function mitraInfo(){
        return $this->httpBuilder->get(self::URL.'/mitra-info');
    }

}