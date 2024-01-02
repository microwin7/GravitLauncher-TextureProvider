<?php

namespace Microwin7\TextureProvider\Data;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Microwin7\PHPUtils\Exceptions\RequiredArgumentMissingException;
use Microwin7\PHPUtils\Main;
use Microwin7\PHPUtils\Security\BearerToken;

class UserFromJWT
{
    /** @return \stdClass The JWT's payload as a PHP object */
    public static function getUserAndValidate(): \stdClass
    {
        $bearerToken = BearerToken::getBearerToken();
        if ($bearerToken === null) throw new RequiredArgumentMissingException('header BearerToken');
        $oOpenSSLAsymmetricKey = openssl_pkey_get_public(Main::getPublicKeyFromBase64());
        if (!$oOpenSSLAsymmetricKey) throw new \ValueError("Необходимо правильно настроить публичный ключ от вашего LaunchServer'а");
        JWT::$leeway = 1000000;
        return JWT::decode(
            $bearerToken,
            new Key($oOpenSSLAsymmetricKey, 'ES256')
        );
    }
}
