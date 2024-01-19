<?php

use Microwin7\PHPUtils\Utils\GDUtils;
use Microwin7\PHPUtils\Utils\Texture;
use Microwin7\TextureProvider\Config;
use Microwin7\PHPUtils\DB\SubDBTypeEnum;
use Microwin7\PHPUtils\Configs\MainConfig;
use Microwin7\TextureProvider\Texture\Cape;
use Microwin7\TextureProvider\Texture\Skin;
use Microwin7\PHPUtils\Configs\TextureConfig;
use Microwin7\PHPUtils\DB\SingletonConnector;
use Microwin7\PHPUtils\Response\JsonResponse;
use Microwin7\TextureProvider\Utils\LuckPerms;
use Microwin7\TextureProvider\Data\UserFromJWT;
use Microwin7\PHPUtils\Exceptions\FileUploadException;
use Microwin7\PHPUtils\Exceptions\TextureSizeHDException;
use Microwin7\PHPUtils\Contracts\User\UserStorageTypeEnum;
use Microwin7\TextureProvider\Request\Loader\RequestParams;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\PHPUtils\Contracts\Texture\Enum\TextureStorageTypeEnum;

// ini_set('error_reporting', E_ALL); // FULL DEBUG
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require_once(__DIR__ . '/../vendor/autoload.php');
// require_once(__DIR__ . '/../../texture-provider/vendor/autoload.php'); // Для выноса за пределы ROOT_FOLDER

// Registration ExceptionHandler
new \Microwin7\PHPUtils\Exceptions\Handler\ExceptionHandler;

$requestParams = new RequestParams;

$JWT_DATA = UserFromJWT::getUserAndValidate();
$requestParams
	->setVariable('username', $JWT_DATA->sub)
	->setVariable('uuid', $JWT_DATA->uuid)
	->withEnum(TextureStorageTypeEnum::STORAGE);

if (isset($_FILES['file'])) {
	$texturePathStorage = Texture::getTexturePathStorage($requestParams->responseType->name);
	if (!is_dir($texturePathStorage)) {
		mkdir($texturePathStorage, 0666, true);
	}
	$file = $_FILES['file'];
	if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) throw new FileUploadException($file['error']);
	if ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) throw new FileUploadException(9);

	$data = file_get_contents($file['tmp_name']);

	(GDUtils::getImageMimeType($data) !== IMAGETYPE_PNG) ? JsonResponse::failed(error: 'Выберите файл в формате .PNG!') : '';
	($file['size'] <= TextureConfig::MAX_SIZE_BYTES) ?: JsonResponse::failed(error: 'Файл превышает размер 2МБ!');

	$image = imagecreatefromstring($data);
	[$w, $h] = [imagesx($image), imagesy($image)];

	try {
		Texture::validateHDSize($w, $h, $requestParams->responseType->name);
		if (Config::USE_LUCKPERMS_PERMISSION_HD_SKIN && (new LuckPerms($requestParams))->getUserWeight() < Config::MIN_WEIGHT)
			JsonResponse::failed(error: 'У вас нет прав на установку плаща! Повысьте свою группу!');
	} catch (TextureSizeHDException $e) {
		Texture::validateSize($w, $h, $requestParams->responseType->name);
	}
	$MODULE_ARRAY_DATA = MainConfig::MODULES['TextureProvider'];
	$table_users = $MODULE_ARRAY_DATA['table_user']['TABLE_NAME'];
	$user_id_column = $MODULE_ARRAY_DATA['table_user']['id_column'];
	$user_uuid_column = $MODULE_ARRAY_DATA['table_user']['uuid_column'];
	if (in_array(Config::USER_STORAGE_TYPE, [UserStorageTypeEnum::DB_USER_ID, UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256])) {
		$user_id = SingletonConnector::get('TextureProvider')->query(<<<SQL
		SELECT $user_id_column FROM $table_users WHERE $user_uuid_column IN (?)
		SQL, "s", $requestParams->uuid)->value();
	}
	$requestParams->setVariable(
		'login',
		match (Config::USER_STORAGE_TYPE) {
			UserStorageTypeEnum::USERNAME => $requestParams->username,
			UserStorageTypeEnum::UUID => $requestParams->uuid,
			UserStorageTypeEnum::DB_USER_ID => $user_id,
			UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => Texture::digest($data)
		}
	);
	$texture = match ($requestParams->responseType) {
		ResponseTypeEnum::SKIN => new Skin(
			textureStorageType: $requestParams->textureStorageType,
			data: $data,
			url: (string) $requestParams,
			isSlim: GDUtils::checkSkinSlimFromImage($image),
			digest: match (Config::USER_STORAGE_TYPE) {
				UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => $requestParams->login,
				default => null
			}
		),
		ResponseTypeEnum::CAPE => new Cape(
			textureStorageType: $requestParams->textureStorageType,
			data: $data,
			url: (string) $requestParams,
			digest: match (Config::USER_STORAGE_TYPE) {
				UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => $requestParams->login,
				default => null
			}
		)
	};
	$filepath = Texture::getTexturePath($requestParams->login, $requestParams->responseType->name);
	if (move_uploaded_file($file['tmp_name'], $filepath)) {
		//chmod($filepath, 0664);
		if (in_array(Config::USER_STORAGE_TYPE, [UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256])) {
			$table_user_assets = $MODULE_ARRAY_DATA['table_user_assets']['TABLE_NAME'];

			$texture_type_column = $MODULE_ARRAY_DATA['table_user_assets']['texture_type_column'];
			$hash_column = $MODULE_ARRAY_DATA['table_user_assets']['hash_column'];
			$texture_meta_column = $MODULE_ARRAY_DATA['table_user_assets']['texture_meta_column'];

			$assets_id_column = $MODULE_ARRAY_DATA['table_user_assets']['id_column'];

			$meta_texture = (string)match ($requestParams->responseType) {
				ResponseTypeEnum::SKIN => (int)$texture->isSlim,
				ResponseTypeEnum::CAPE => 0
			};
			SingletonConnector::get('TextureProvider')->query(
				<<<SQL
				INSERT INTO $table_user_assets ($assets_id_column, $texture_type_column, $hash_column, $texture_meta_column)
				VALUES (?, ?, ?, ?)
			SQL .
					match (MainConfig::DB_SUD_DB) {
						SubDBTypeEnum::MySQL => <<<SQL
				ON DUPLICATE KEY UPDATE
			SQL,
						SubDBTypeEnum::PostgreSQL => <<<SQL
				ON CONFLICT ($assets_id_column, $texture_type_column) DO UPDATE SET
			SQL
					}
					.
					<<<SQL
				$hash_column = ?, $texture_meta_column = ?
			SQL,
				"ssssss",
				$user_id,
				$requestParams->responseType->name,
				$requestParams->login,
				$meta_texture,
				$requestParams->login,
				$meta_texture
			);
		}
		JsonResponse::response($texture);
	} else JsonResponse::failed(error: 'Произошла ошибка перемещения файла');
} else JsonResponse::failed(error: 'Файл не был загружен!');
