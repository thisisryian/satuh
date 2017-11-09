<?php
namespace satuh;
use InvalidArgumentException;
use Guzzlehttps\Psr7;
use Guzzlehttps\Exception\BadResponseException as GuzzleException;
use Exception;
use satuh\httpBuilder;
class auth
{
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
    protected $httpBuilder;
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

    protected $defaultHeaders = array(
        'Authorization: ',
        'Accept: application/json'

    );

    public function __construct($client_id = null,$client_secret=null,$redirect_uri=null)
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
        $this->httpBuilder = new httpBuilder();

    }

    public function setAuthorization($authorization){
        if($authorization != null){
            $this->defaultHeaders[0] .= "Bearer ".urlencode($authorization);
        }
        $this->httpBuilder->setHeaders($this->defaultHeaders);
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

    public function asArray(){
        $this->httpBuilder->asArray();
    }

    public function asJson(){
        $this->httpBuilder->asJson();
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

        $this->httpBuilder->setHeaders($this->defaultHeaders);
        $response = json_decode($this->httpBuilder->post(self::TOKEN_URI,$params));
        $data = json_decode($response,true);
        $httpCode = $this->httpBuilder->getHttpCode();
        if ($httpCode >= 400) throw  new Exception( implode("\n",$data) ? : $response,$httpCode);
        if(isset($data['access_token'])){
            $this->accessToken = $data["access_token"];
        }
        return $data;

    }

    public function setAccessToken($access_token){
        $this->accessToken = $access_token;
    }

    public function getUserData($accessToken){
        $this->setAuthorization($accessToken);
        $res = $this->httpBuilder->get(self::USER_URI);
        return $res;
    }

    public function logout(){
        $this->httpBuilder->setHeaders($this->defaultHeaders);
        $res = $this->httpBuilder->get(self::LOGOUT_URI);
        return $res;
    }

}