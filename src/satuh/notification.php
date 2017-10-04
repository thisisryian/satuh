<?php
namespace satuh;
use InvalidArgumentException;
use Exception;
use satuh\auth;
class notification
{
    protected $curlHandle;
    const TOKEN_URI = "https://account.satuh.com/oauth/token";
    const insertAndroidToken_URI = "https://account.satuh.com/api/push-notification/insert-android-token";
    const insertIosToken_URI = "https://account.satuh.com/api/push-notification/insert-ios-token";
    const updateAndroidToken_URI = "https://account.satuh.com/api/push-notification/update-android-token";
    const updateIosToken_URI = "https://account.satuh.com/api/push-notification/update-ios-token";
    const sendNotificationProject_URI = "https://account.satuh.com/api/push-notification/send-notification-project";
    const sendAndroidNotification_URI = "https://account.satuh.com/api/push-notification/send-notification-android";
    const sendIosNotification_URI = "https://account.satuh.com/api/push-notification/send-notification-ios";
    protected $accessToken = null;
    protected $client_id;
    protected $client_secret;
    

    public $headers = array(
        'Authorization: ',
        'Accept: application/json'

    );

    function __construct($client_id,$client_secret)
    {
        if (empty($client_id)) throw new InvalidArgumentException("Client Id is not specified");
        if (empty($client_secret)) throw new InvalidArgumentException("Client Secret is not specified");

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
        }
    }

    protected function curlSetGet($url)
    {

        curl_setopt( $this->curlHandle, CURLOPT_HTTPGET, 1 );
        curl_setopt($this->curlHandle,CURLOPT_URL,$url);
    }

    protected function curlSetPost($url,$params)
    {
        curl_setopt( $this->curlHandle, CURLOPT_POST, 1 );
        curl_setopt($this->curlHandle,CURLOPT_URL,$url);
        curl_setopt($this->curlHandle,CURLOPT_POSTFIELDS, $params);
    }

    private function setupCurl(){
        if (empty($this->accessToken))throw new InvalidArgumentException("Please configured your access token");
        $this->headers[0] .= "Bearer ".urlencode($this->accessToken);
        curl_setopt( $this->curlHandle, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $this->curlHandle, CURLOPT_RETURNTRANSFER, true );

    }

    protected function exec()
    {
        $result = curl_exec($this->curlHandle);
        return $result;
    }

    public function setAccessToken($access_token){
        $this->accessToken = $access_token;
    }

    public function getAccessToken(){
        return $this->accessToken;
    }

    public function insertAndroidToken($fcm_id,$project,$account_id = null){
        if (!$fcm_id) throw new InvalidArgumentException("fcm_id is not specified");
        if (!$project) throw new InvalidArgumentException("project is not specified");

        $token_data = [
            'fcm_id' => $fcm_id,
            'project' => $project,
            'account_id' => $account_id,
        ];
        $this->setupCurl();
        $this->curlSetPost(self::insertAndroidToken_URI,($token_data));
        $response = json_decode($this->exec(),true);
        return $response;
    }

    public function insertIosToken($apns_id,$project,$account_id = null){
        if (empty($apns_id)) throw new InvalidArgumentException("Ios Token is not specified");
        if (empty($project)) throw new InvalidArgumentException("Project is not specified");


        $token_data =[
            'apns_id' => $apns_id,
            'project' => $project,
            'account_id' => $account_id,
        ];
        $this->setupCurl();
        $this->curlSetPost(self::insertIosToken_URI,$token_data);
        $response = json_decode($this->exec(),true);
        return $response;
    }

    public function updateAndroidToken($token,$account_id){
        if (empty($token)) throw new InvalidArgumentException("Android Token is not specified");
        if (empty($account_id)) throw new InvalidArgumentException("Account Id is not specified");
        $token_data =[
            'account_id' => $account_id,
        ];
        $this->setupCurl();
        $this->curlSetPost(self::updateAndroidToken_URI."/".$token,$token_data);
        $response = json_decode($this->exec(),true);
        return $response;
    }

    public function updateIosToken($token,$account_id){
        if (empty($token)) throw new InvalidArgumentException("Android Token is not specified");
        if (empty($acount_id)) throw new InvalidArgumentException("Account Id is not specified");

        $token_data =[
            'account_id' => $account_id,
        ];
        $this->setupCurl();
        $this->curlSetPost(self::updateIosToken_URI."/".$token,$token_data);
        $response = json_decode($this->exec(),true);
        return $response;
    }

    public function sendNotificationProject($project,$content){
        if (empty($project)) throw new InvalidArgumentException("Project is not specified");
        if (empty($content)) throw new InvalidArgumentException("Please set your content message");

        $push_notification =[
            'project' => $project,
            'content' => http_build_query($content)
        ];
        $this->setupCurl();
        $this->curlSetPost(self::sendNotificationProject_URI,$push_notification);
        $response = json_decode($this->exec(),true);
        return $response;
    }

    public function sendAndroidNotification($token,$content){
        if (empty($token)) throw new InvalidArgumentException("token is not specified");
        if (empty($content)) throw new InvalidArgumentException("Please set your content message");
        $push_notification =[
            'token' => $token,
            'content' => http_build_query($content)
        ];
        $this->setupCurl();
        $this->curlSetPost(self::sendAndroidNotification_URI,$push_notification);
        $response = json_decode($this->exec(),true);
        return $response;
    }

    public function sendIosNotification($token,$content){
        if (empty($token)) throw new InvalidArgumentException("token is not specified");
        if (empty($content)) throw new InvalidArgumentException("Please set your content message");
        $push_notification =[
            'token' => $token,
            'content' => http_build_query($content)
        ];
        $this->setupCurl();
        $this->curlSetPost(self::sendIosNotification_URI,$push_notification);
        $response = json_decode($this->exec(),true);
        return $response;
    }
}