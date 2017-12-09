<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 05/12/2017
 * Time: 09.06
 */

namespace satuh;
use InvalidArgumentException;

class sms
{
    protected $httpBuilder;
    protected $ENVIRONMENT;
    protected $cachePath;
    protected $username;
    protected $password;
    protected $defaultHeaders = [];

    public function __construct($username,$password,$environment)
    {
        if (empty($username)) throw new InvalidArgumentException("Username is not specified");
        if (empty($password)) throw new InvalidArgumentException("Password set your environment");
        if (empty($environment)) throw new InvalidArgumentException("Please set your environment");

        $this->setEnvironment($environment);
        $this->username = $username;
        $this->password = $password;
        $this->httpBuilder = new httpBuilder();
        $this->httpBuilder->setHeaders($this->defaultHeaders);
    }

    public function setEnvironment($environment){
        if($environment !== "production" && $environment !== "testing") throw new InvalidArgumentException("Environment could only be set to production or testing");
        $this->ENVIRONMENT = $environment;
    }
    function asArray(){
        $this->httpBuilder->asArray();
    }

    function asJson(){
        $this->httpBuilder->asJson();
    }
    public function send_sms($to,$message,$default_provider = true)
    {
        if($this->ENVIRONMENT == 'testing')
        {
            $data="To : ".$to."<br>";
            $data .="message : ".$message."<br>";

            file_put_contents($this->cachePath. time().'_'. ".html", $data);
        }

        if($this->ENVIRONMENT == 'production')
        {
            if($default_provider){
                return $this->sms_gateway($to,$message);
            }else{
                return $this->go_sms_gateway($to,$message);
            }

        }
    }


    private function sms_gateway($to,$msg)
    {

        $auth=MD5($this->username.$this->password.$to);
        $msg=urlencode($msg);
        $response = $this->httpBuilder->get("http://send.smsgateway.co.id:8080/web2sms/api/SendSMS.aspx?username=".$this->username."&mobile=".$to."&message=".$msg."&auth=".$auth);
        if(count($response) == 4){
            return false;
        }else{
            return true;
        }
    }

    private function go_sms_gateway($to,$message){
        $response = $this->httpBuilder->get('https://secure.gosmsgateway.com/api/Send.php?username='.$this->username.'&mobile='.$to.'&message='.urlencode($message).'&password='.$this->password);
        if (strpos($response, '1701') !== false) {
            return true;
        }else{
            return false;
        }
    }
}