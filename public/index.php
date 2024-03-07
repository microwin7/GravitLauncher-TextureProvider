<?php

use Microwin7\TextureProvider\Data\User;
use Microwin7\PHPUtils\Security\BearerToken;
use Microwin7\PHPUtils\Response\JsonResponse;
use Microwin7\TextureProvider\Texture\Texture;
use Microwin7\TextureProvider\Request\Provider\RequestParams;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\PHPUtils\Exceptions\ValidateBearerTokenException;

// ini_set('error_reporting', E_ALL); // FULL DEBUG
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require_once(__DIR__ . '/../vendor/autoload.php');
// require_once(__DIR__ . '/../../texture-provider/vendor/autoload.php'); // Для выноса за пределы ROOT_FOLDER

// Registration ExceptionHandler
new \Microwin7\PHPUtils\Exceptions\Handler\ExceptionHandler;

$requestParams = RequestParams::fromRequest();
if ($requestParams->responseType === ResponseTypeEnum::JSON) BearerToken::validateBearer() ?: throw new ValidateBearerTokenException;
JsonResponse::response(new Texture(new User($requestParams)));
