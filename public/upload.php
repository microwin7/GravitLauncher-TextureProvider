<?php

use Microwin7\PHPUtils\Response\JsonResponse;
use Microwin7\TextureProvider\Texture\Texture;
use Microwin7\TextureProvider\Data\UserFromJWT;
use Microwin7\PHPUtils\Exceptions\FileUploadException;
use Microwin7\TextureProvider\Request\Loader\RequestParams;

// ini_set('error_reporting', E_ALL); // FULL DEBUG
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require_once(__DIR__ . '/../vendor/autoload.php');
// require_once(__DIR__ . '/../../texture-provider/vendor/autoload.php'); // Для выноса за пределы ROOT_FOLDER

// Registration ExceptionHandler
new \Microwin7\PHPUtils\Exceptions\Handler\ExceptionHandler;

// Token signature verification and get username, uuid out JWT
$JWT_DATA = UserFromJWT::getUserAndValidate();

if (isset($_FILES['file'])) {
	JsonResponse::response(
		Texture::loadTexture(
			/** AutoInit ResponseTypeEnum from request, validate after only SKIN or CAPE */
			($requestParams = new RequestParams)
				/** Variable username for UserStorageTypeEnum::USERNAME in Config::USER_STORAGE_TYPE */
				->setVariable('username', $JWT_DATA->sub)
				/** Variable uuid for other enum types in Config::USER_STORAGE_TYPE */
				->setVariable('uuid', $JWT_DATA->uuid),
			$_FILES['file']
		)
	);
} else JsonResponse::failed(error: throw new FileUploadException(UPLOAD_ERR_NO_FILE));
