<?php

namespace Microwin7\TextureProvider\Data;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Microwin7\PHPUtils\Main;
use Microwin7\PHPUtils\Security\BearerToken;
use Microwin7\PHPUtils\Exceptions\RequiredArgumentMissingException;

class UserDataFromJWT
{
    /** @return \stdClass The JWT's payload as a PHP object */
    public static function getUserAndValidate(): \stdClass
    {
        $bearerToken = BearerToken::getBearerToken();
        if ($bearerToken === null) throw new RequiredArgumentMissingException('header BearerToken');
        $oOpenSSLAsymmetricKey = openssl_pkey_get_public(Main::getLaunchServerPublicKey());
        if (!$oOpenSSLAsymmetricKey) throw new \ValueError("Необходимо правильно настроить публичный ключ от вашего LaunchServer'а");
        return JWT::decode(
            $bearerToken,
            new Key($oOpenSSLAsymmetricKey, 'ES256')
        );
    }
}
