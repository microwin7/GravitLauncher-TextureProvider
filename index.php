<?php
ini_set('error_reporting', E_ALL); // FULL DEBUG
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use Microwin7\TextureProvider\Data\User;
use Microwin7\PHPUtils\Security\BearerToken;
use Microwin7\PHPUtils\Response\JsonResponse;
use Microwin7\TextureProvider\Texture\Texture;
use Microwin7\TextureProvider\Request\Provider\RequestParams;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;

require_once(__DIR__ . '/vendor/autoload.php');

try {
    $requestParams = RequestParams::fromRequest();
    if ($requestParams->responseType === ResponseTypeEnum::JSON) BearerToken::validateBearer() ?: throw new ValueError('Incorrect BearerToken');
    JsonResponse::response(new Texture(new User($requestParams)));
} catch (Throwable $e) {
    JsonResponse::failed(error: $e->getMessage());
}
