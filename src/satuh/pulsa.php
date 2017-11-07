<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 07/11/2017
 * Time: 10.04
 */

namespace satuh;
use httpBuilder;
use InvalidArgumentException;
class pulsa
{
    const URL = "https://epay.satuh.com/API/pulsa";
    protected $accessToken = null;
    protected $clientId;
    protected $clientSecret;
    protected $httpBuilder;
    protected $defaultHeaders = array(
        'Authorization: ',
        'Accept: application/json'
    );

    public function __construct($client_id,$client_secret)
    {
        if (empty($client_id)) throw new InvalidArgumentException("Client Id is not specified");
        if (empty($client_secret)) throw new InvalidArgumentException("Client Secret is not specified");

        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
        $this->curlHandle = curl_init();
        $this->authorization();
        $this->httpBuilder = new httpBuilder();
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

    function getPulsaList($phone){
        if (empty($phone)) throw new InvalidArgumentException("Phone is not specified");
        return $this->httpBuilder->post(self::URL,['phone'=>$phone]);
    }

    function payment($product_id,$price,$phone){
        if (empty($product_id)) throw new InvalidArgumentException("Product_id is not specified");
        if (empty($price)) throw new InvalidArgumentException("Price is not specified");
        if (empty($phone)) throw new InvalidArgumentException("Phone is not specified");
        return $this->httpBuilder->post(self::URL.'/payment',['phone'=>$phone,'product_id'=> $product_id,'price' => $price]);
    }

    function checkTransaction($trx_id,$phone){
        if (empty($trx_id)) throw new InvalidArgumentException("Transaction Id is not specified");
        if (empty($phone)) throw new InvalidArgumentException("Phone is not specified");
        return $this->httpBuilder->post(self::URL.'/check-transaction',['phone'=>$phone,'trxID' => $trx_id]);
    }


}