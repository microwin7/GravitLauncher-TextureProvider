<?php
ini_set('error_reporting', E_ALL); // FULL DEBUG
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use Microwin7\TextureProvider\Data\User;
use Microwin7\PHPUtils\Response\Response;
use Microwin7\PHPUtils\Security\BearerToken;
use Microwin7\TextureProvider\Data\ResponseTypeEnum;
use Microwin7\TextureProvider\Texture\Texture;
use Microwin7\TextureProvider\Utils\RequestParams;

require_once(__DIR__ . '/vendor/autoload.php');

try {
    $requestParams = RequestParams::fromRequest();
    if ($requestParams->responseType === ResponseTypeEnum::JSON) BearerToken::validationBearer() ?: throw new ValueError('Incorrect BearerToken');
    Response::response(new Texture(new User($requestParams)));
} catch (Throwable $e) {
    Response::failed(error: $e->getMessage());
}
