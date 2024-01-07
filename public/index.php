<?php

use Microwin7\TextureProvider\Data\User;
use Microwin7\PHPUtils\Security\BearerToken;
use Microwin7\PHPUtils\Response\JsonResponse;
use Microwin7\TextureProvider\Texture\Texture;
use Microwin7\TextureProvider\Request\Provider\RequestParams;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;

require_once(__DIR__ . '/../vendor/autoload.php');

// Registration ExceptionHandler
new \Microwin7\PHPUtils\Exceptions\Handler\ExceptionHandler;

$requestParams = RequestParams::fromRequest();
if ($requestParams->responseType === ResponseTypeEnum::JSON) BearerToken::validateBearer() ?: throw new ValueError('Incorrect BearerToken');
JsonResponse::response(new Texture(new User($requestParams)));
