<?php
namespace satuh;
use InvalidArgumentException;
use satuh\auth;
use satuh\httpBuilder;
class notification
{
    const TOKEN_URI = "https://account.satuh.com/oauth/token";
    const insertToken_URI = "https://account.satuh.com/api/push-notification/insert-android-token";
    const sendNotificationProject_URI = "https://account.satuh.com/api/push-notification/send-notification-project";
    const deleteToken_URI = "https://account.satuh.com/api/push-notification/delete-token";
    protected $accessToken = null;
    protected $clientId;
    protected $clientSecret;
    protected $httpBuilder;
    public $defaultHeaders = array(
        'Authorization: ',
        'Accept: application/json'
    );

    function __construct($client_id,$client_secret)
    {
        if (empty($client_id)) throw new InvalidArgumentException("Client Id is not specified");
        if (empty($client_secret)) throw new InvalidArgumentException("Client Secret is not specified");

        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
        $this->httpBuilder = new httpBuilder();
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

    public function setAccessToken($access_token){
        $this->accessToken = $access_token;
    }

    public function getAccessToken(){
        return $this->accessToken;
    }

    public function insertToken($token,$project,$account_id = null){
        if (!$token) throw new InvalidArgumentException("token is not specified");
        if (!$project) throw new InvalidArgumentException("project is not specified");

        $token_data = [
            'fcm_id' => $token,
            'project' => $project,
            'account_id' => $account_id,
        ];
        $response = $this->httpBuilder->post(self::insertToken_URI,($token_data));
        return $response;
    }

    public function sendNotificationProject($project,$content,$server_key,$server_id){
        if (empty($project)) throw new InvalidArgumentException("Project is not specified");
        if (empty($content)) throw new InvalidArgumentException("Please set your content message");
        if (empty($server_key)) throw new InvalidArgumentException("Server Key is not specified");
        if (empty($server_id)) throw new InvalidArgumentException("Server Id is not specified");
        $push_notification =[
            'project' => $project,
            'content' => http_build_query($content),
            'server_id' => $server_id,
            'server_key' => $server_key
        ];
        $response = $this->httpBuilder->post(self::sendNotificationProject_URI,$push_notification);
        return $response;
    }

    public function sendPersonalNotification($project,$content,$account_id,$server_key,$server_id){
        if (empty($project)) throw new InvalidArgumentException("Project is not specified");
        if (empty($content)) throw new InvalidArgumentException("Please set your content message");
        if (empty($account_id)) throw new InvalidArgumentException("Account Id is not specified");
        if (empty($server_key)) throw new InvalidArgumentException("Server Key is not specified");
        if (empty($server_id)) throw new InvalidArgumentException("Server Id is not specified");

        $push_notification =[
            'project' => $project,
            'content' => http_build_query($content),
            'account_id' => $account_id,
            'server_id' => $server_id,
            'server_key' => $server_key
        ];
        $response = $this->httpBuilder->post(self::sendNotificationProject_URI,$push_notification);
        return $response;
    }
  
    public function deleteToken($token,$project,$account_id = null){
        if (!$token) throw new InvalidArgumentException("token is not specified");
        if (!$project) throw new InvalidArgumentException("project is not specified");

        $token_data = [
            'project' => $project,
            'account_id' => $account_id,
        ];
        $response = $this->httpBuilder->post(self::deleteToken_URI.'/'.urlencode($token),($token_data));
        return $response;
    }
}