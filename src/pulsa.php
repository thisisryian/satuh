<?php
namespace satuh;
use InvalidArgumentException;
class pulsa
{
    const URL = "https://epay.satuh.com/api/pulsa";
    protected $accessToken = null;
    protected $clientId;
    protected $clientSecret;
    protected $httpBuilder;
    protected $ENVIRONMENT;
    protected $defaultHeaders = array(
        'Authorization: ',
        'Accept: application/json',
        'Environment: '
    );

    public function __construct($client_id,$client_secret,$environment)
    {
        if (empty($client_id)) throw new InvalidArgumentException("Client Id is not specified");
        if (empty($client_secret)) throw new InvalidArgumentException("Client Secret is not specified");
        if (empty($environment)) throw new InvalidArgumentException("Please set your environment");

        $this->setEnvironment($environment);

        $this->httpBuilder = new httpBuilder();
        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
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

    public function setEnvironment($environment){

        if($environment !== "production" && $environment !== "testing") throw new InvalidArgumentException("Environment could only be set to production or testing");
        $this->ENVIRONMENT = $environment;
        $this->defaultHeaders[2] .= $this->ENVIRONMENT;
    }

    function asArray(){
        $this->httpBuilder->asArray();
    }

    function asJson(){
        $this->httpBuilder->asJson();
    }

    function providers(){
        return $this->httpBuilder->get(self::URL);
    }

    function products($provider_id){
        if (empty($provider_id)) throw new InvalidArgumentException("Provider ID is not specified");
        return $this->httpBuilder->get(self::URL.'/product/'.$provider_id);
    }

    function productDetail($product_id){
        if (empty($product_id)) throw new InvalidArgumentException("Product ID is not specified");
        return $this->httpBuilder->get(self::URL.'/product-detail/'.$product_id);
    }

    function transaction($product_id,$phone){
        if (empty($product_id)) throw new InvalidArgumentException("Product ID is not specified");
        if (empty($phone)) throw new InvalidArgumentException("Phone is not specified");
        return $this->httpBuilder->post(self::URL.'/transaction',['phone'=>$phone,'product_id'=> $product_id]);

    }

    function transactionDetail($pulsa_transaction_id){
        if (empty($pulsa_transaction_id)) throw new InvalidArgumentException("Pulsa Transaction ID is not specified");
        return $this->httpBuilder->get(self::URL.'/transaction-detail/'.$pulsa_transaction_id);
    }

    function transactionHistory(){
        return $this->httpBuilder->get(self::URL.'/transaction-history');
    }

}