# satuh

Satuh Library For PHP

## Description ##
Satuh Libarry enables you to work with Satuh APIs such as Satuh Authentication, Satuh SMS, Satuh Notification, Satuh PLN, Satuh Voucher Game, or Satuh Pulsa

## Requirements ##
* [PHP 7.0.0 or higher](http://www.php.net/)

## Installation ##
Install this libary through  [composer](https://getcomposer.org).

    composer require pmb/satuh:^0.3.0

## Usage ##
### satuh\auth
satuh\auth is authentication to access our lib. Also to grant our Satuh User information, which u can access it for your login application.
#### Basic Example ####

```php
<?php
use satuh\auth;

$satuh = new auth("CLIENT_ID","CLIENT_SECRET","CLIENT_REDIRECT_URI");
$satuh->authorize('code');
//this action will give u an authenticaion code in your CLIENT_REDIRECT_URI
$code = $_GET['code'];
$satuh->setOauthCode($code);
$accessToken = $satuh->getAccessToken('authorization_code');
$user = $satuh->getUserData($accessToken['access_token']);
print_r($user);
```
## Client Credential
> Only Cooperate Vendor could use this.
### satuh\pulsa

#### Basic Examples ###
```php
$pulsa = new pulsa("CLIENT_ID","CLIENT_SECRET","ENVIRONMENT");
```

### satuh\pln

#### Basic Examples ###
```php
$pln = new pln("CLIENT_ID","CLIENT_SECRET","ENVIRONMENT");
```

### satuh\game

#### Basic Examples ###
```php
$game = new game("CLIENT_ID","CLIENT_SECRET","ENVIRONMENT");
```

## More Information

For more information, see the official [API documents]. If it's your first time using this library, we recommend taking a look at our official [API documents]

## End Of Client Credential

### Part Of Satuh

### satuh\sms

#### Basic Examples ###
```php
$sms = new sms("USERNAME","PASSWORD","ENVIRONMENT");
$reponse = $sms->send_sms("phone","message",true)
```
