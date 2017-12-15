<?php
namespace satuh;
use InvalidArgumentException;

class game
{
    const URL = "https://epay.satuh.com/api/voucher-game";
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

    function getVoucherList(){
        return $this->httpBuilder->get(self::URL);
    }

    function payment($product_id,$amount){
        if (empty($product_id)) throw new InvalidArgumentException("Product_id is not specified");
        if (empty($amount)) throw new InvalidArgumentException("Price is not specified");
        return $this->httpBuilder->post(self::URL.'/payment',['product_id'=> $product_id, 'amount'=> $amount]);
    }

    function checkTransaction($voucher_game_request_id){
        if (empty($voucher_game_request_id)) throw new InvalidArgumentException("Voucher Game Request Id is not specified");
        return $this->httpBuilder->post(self::URL.'/check-transaction',['voucher_game_request_id'=> $voucher_game_request_id]);
    }

    function productAvailable($product_id){
        if (empty($product_id)) throw new InvalidArgumentException("Product_id is not specified");
        return $this->httpBuilder->post(self::URL.'/check-product-id',['product_id'=> $product_id]);
    }

    function mitraInfo(){
        return $this->httpBuilder->get(self::URL.'/mitra-info');
    }
}