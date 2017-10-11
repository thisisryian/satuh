<?php
namespace satuh;
use InvalidArgumentException;
use Guzzlehttps\Psr7;
use Guzzlehttps\Exception\BadResponseException as GuzzleException;
use Exception;
class auth
{
    protected $curlHandle;
    const AUTHO_URI = "https://account.satuh.com/oauth/authorize";
    const TOKEN_URI = "https://account.satuh.com/oauth/token";
    const USER_URI = "https://account.satuh.com/api/user";
    const LOGOUT_URI = "https://account.satuh.com/api/logout";
    private $redirectUri = null;
    private $clientSecret;
    private $clientId;
    private $scope;
    protected $accessToken = null;
    protected $tokenId;
    protected $username;
    protected $password;
    protected $oauthCode;
    /**
     * Create a new OAuthCredentials.
     *
     * The configuration array accepts various options
     *
     * - clientId
     *   A unique identifier issued to the client to identify itself to the
     *   authorization server.
     *
     * - clientSecret
     *   A shared symmetric secret issued by the authorization server,
     *   which is used to authenticate the client.
     *
     * - scope
     *   The scope of the access request, expressed either as an Array
     *   or as a space-delimited String.
     *
     * - redirectUri
     *   The redirection URI used in the initial request.
     *
     * - accessToken
     *   The current access token for this client.
     *
     * @param array $config Configuration array
     */

    public $_defaultHeaders = array(
        'Authorization: ',
        'Accept: application/json'

    );

    function __construct($client_id = null,$client_secret=null,$redirect_uri=null)
    {
        if(!empty($client_id)){
            $this->clientId = $client_id;
        }
        if(!empty($client_secret)){
            $this->clientSecret = $client_secret;
        }
        if(!empty($redirect_uri)){
            $this->redirectUri = $redirect_uri;
        }

        $this->curlHandle = curl_init();

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

    private function setupCurl($authorization = null){
        if($authorization != null){
            $this->_defaultHeaders[0] .= "Bearer ".urlencode($authorization);

        }
        curl_setopt( $this->curlHandle, CURLOPT_HTTPHEADER, $this->_defaultHeaders);
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $this->curlHandle, CURLOPT_RETURNTRANSFER, true );
    }

    protected function exec()
    {
        $result = curl_exec($this->curlHandle);
        return $result;
    }


    private function isAbsoluteUri($uri)
    {
        $uri = $this->coerceUri($uri);

        return $uri->getScheme() && ($uri->getHost() || $uri->getPath());
    }

    private function coerceUri($uri)
    {
        if (is_null($uri)) {
            return;
        }

        return Psr7\uri_for($uri);
    }

    public function setClientId($client_id){
        return $this->clientId = $client_id;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function setClientSecret($client_secret){
        return$this->clientSecret = $client_secret;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function setRedirectUri($uri = null){
        if (is_null($uri)) {
            $this->redirectUri = null;

            return;
        }
        // redirect URI must be absolute
        if (!$this->isAbsoluteUri($uri)) {
            if ('postmessage' !== (string)$uri) {
                throw new InvalidArgumentException(
                    'Redirect URI must be absolute');
            }
        }
       return $this->redirectUri = (string)$uri;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function setScope($scope = null)
    {
        if (is_null($scope)) {
            $this->scope = "";
        } elseif (is_string($scope)) {
            $this->scope = explode(' ', $scope);
        } elseif (is_array($scope)) {
            foreach ($scope as $s) {
                $pos = strpos($s, ' ');
                if ($pos !== false) {
                    throw new InvalidArgumentException(
                        'array scope values should not contain spaces');
                }
            }
            $this->scope = $scope;
        } else {
            throw new InvalidArgumentException(
                'scopes should be a string or array of strings');
        }
    }

    public function getScope()
    {
        return $this->scope;
    }


    public function authorize($response_type){
        $config = [
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => $response_type,
            'scope' => '',
        ];

        $query = http_build_query(
            $config
        );
        $url = self::AUTHO_URI."?".$query;
        header( "Location: {$url}" );
        exit();

    }

    public function setUsername($username){
        $this->username = $username;
    }

    public function setPassword($password){
        $this->password = $password;
    }

    public function setOauthCode($code){
        $this->oauthCode = $code;
    }


    public function getAccessToken($grant_type){
        $params = [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'redirect_uri' => $this->getRedirectUri(),
            'scope' => '',
            'grant_type' => ''
        ];

        if($grant_type == 'password'){
            if (empty($this->password)) throw new \InvalidArgumentException("Password is not specified");
            if (empty($this->username)) throw new \InvalidArgumentException("Account Id is not specified");
            $params['grant_type'] = 'password';
            $params['username'] = $this->username;
            $params['password'] = $this->password;

        }else if($grant_type == 'client'){
            $params['grant_type'] = 'client_credentials';
        }else {
            if (empty($this->oauthCode)) throw new \InvalidArgumentException("Oauth Code is not specified");
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $this->oauthCode;
        }

        $this->setupCurl();
        $this->curlSetPost(self::TOKEN_URI,$params);

        $response = $this->exec();

        $data = json_decode($response,true);

        $httpCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        if ($httpCode >= 400) throw  new Exception( implode("\n",$data) ? : $response,$httpCode);
        if(isset($data['access_token'])){
            $this->accessToken = $data["access_token"];
        }
        $data['status'] = true;
        return $data;

    }

    public function setAccessToken($access_token){
        $this->accessToken = $access_token;
    }


    public function getUserData($accessToken){
        $this->setupCurl($accessToken);
        $this->curlSetGet(self::USER_URI);
        $res=$this->exec();
        $res = json_decode($res,true);
        $res['status'] = true;
        return $res;
    }

    public function logout(){
        $this->setupCurl($this->accessToken);
        $this->curlSetGet(self::LOGOUT_URI);
        return $this->exec();
    }

}