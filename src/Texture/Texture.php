<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture;

define('PNG_FILE_SELECTION', 'Выберите файл в формате .PNG!');
define('FILE_SIZE_EXCEED', 'Файл превышает размер 2МБ!');
define('NO_HD_SKIN_PERMISSION', 'У вас нет прав на установку HD скина!');
define('NO_HD_CAPE_PERMISSION', 'У вас нет прав на установку HD плаща!');
define('FILE_MOVE_FAILED', 'Произошла ошибка перемещения файла');
define('FILE_NOT_UPLOADED', 'Файл не был загружен!');

use stdClass;
use JsonSerializable;
use Microwin7\TextureProvider\Config;
use Microwin7\PHPUtils\DB\SubDBTypeEnum;
use Microwin7\TextureProvider\Data\User;
use Microwin7\PHPUtils\Configs\MainConfig;
use Microwin7\PHPUtils\Configs\PathConfig;
use Microwin7\TextureProvider\Texture\Cape;
use Microwin7\TextureProvider\Texture\Skin;
use Microwin7\TextureProvider\Utils\GDUtils;
use Microwin7\PHPUtils\Configs\TextureConfig;
use Microwin7\PHPUtils\DB\SingletonConnector;
use Microwin7\TextureProvider\Utils\LuckPerms;
use function Microwin7\PHPUtils\str_ends_with_slash;
use Microwin7\PHPUtils\Utils\Texture as TextureUtils;
use Microwin7\PHPUtils\Exceptions\FileUploadException;
use Microwin7\PHPUtils\Exceptions\TextureLoaderException;
use Microwin7\PHPUtils\Exceptions\TextureSizeHDException;
use Microwin7\TextureProvider\Texture\Storage\MojangType;
use Microwin7\PHPUtils\Contracts\User\UserStorageTypeEnum;
use Microwin7\TextureProvider\Texture\Storage\DefaultType;
use Microwin7\TextureProvider\Texture\Storage\StorageType;
use Microwin7\PHPUtils\Contracts\Texture\Enum\MethodTypeEnum;
use Microwin7\TextureProvider\Request\Provider\RequestParams;
use Microwin7\TextureProvider\Texture\Storage\CollectionType;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\PHPUtils\Exceptions\RequiredArgumentMissingException;
use Microwin7\PHPUtils\Contracts\Texture\Enum\TextureStorageTypeEnum;
use Microwin7\TextureProvider\Request\Loader\RequestParams as LoaderRequestParams;

class Texture implements JsonSerializable
{
    public              ?Skin                       $skin = null;
    public              ?Cape                       $cape = null;
    public              ?string                     $skinID = null;
    public              ?string                     $capeID = null;

    private             TextureStorageTypeEnum      $textureStorageType;

    public function __construct(
        public          User                        $user
    ) {
        $this->textureStorageType = $this->user->textureStorageType;
        if ($this->textureStorageType === TextureStorageTypeEnum::STORAGE) {
            /** Для JSON $skinID и $capeID генерируются на основе username и uuid */
            if ($this->user->responseType === ResponseTypeEnum::JSON) $this->generateTextureID();
            /**
             * Для конечных текстур, в запросе уже будет вложен login,
             * который будет использоваться для получения конкретной текстуры из такого же хранилища,
             * при котором был сгенерирован JSON
             */
            if ($this->user->responseType === ResponseTypeEnum::SKIN) $this->skinID = $this->user->login;
            if ($this->user->responseType === ResponseTypeEnum::CAPE) $this->capeID = $this->user->login;
        }
        $this->findData();
        // RESPONSE 404 null texture
        if ($this->user->responseType !== ResponseTypeEnum::JSON) $this->ResponseTexture(null);
    }

    private function findData(): void
    {
        if (
            $this->textureStorageType === TextureStorageTypeEnum::STORAGE &&
            $this->user->methodType !== MethodTypeEnum::MOJANG
        ) $this->storage();

        if ($this->textureStorageType === TextureStorageTypeEnum::STORAGE) $this->nextTextureStorage();

        /**
         * Mojang используется только при генерации JSON,
         * при получении текстуры он обходится
         */
        if (
            $this->user->responseType === ResponseTypeEnum::JSON &&
            $this->textureStorageType === TextureStorageTypeEnum::MOJANG &&
            in_array($this->user->methodType, [MethodTypeEnum::MOJANG, MethodTypeEnum::HYBRID])
        ) $this->mojang();

        if ($this->textureStorageType === TextureStorageTypeEnum::MOJANG) $this->nextTextureStorage();

        if (
            Config::GIVE_FROM_COLLECTION &&
            $this->skin === null &&
            $this->textureStorageType === TextureStorageTypeEnum::COLLECTION
        ) $this->collection();

        if ($this->textureStorageType === TextureStorageTypeEnum::COLLECTION) $this->nextTextureStorage();

        if (
            (Config::GIVE_DEFAULT_SKIN || Config::GIVE_DEFAULT_CAPE) &&
            $this->textureStorageType === TextureStorageTypeEnum::DEFAULT
        ) $this->default();
    }

    private function storage(): void
    {
        $this->setTextures(new StorageType($this->skinID, $this->capeID, $this->user->responseType));
    }
    private function mojang(): void
    {
        $this->setTextures(new MojangType($this->user->username ?? throw new RequiredArgumentMissingException('username'), $this->skin ? true : false, $this->cape ? true : false));
    }
    private function collection(): void
    {
        $this->setTextures(new CollectionType($this->user->uuid ?? throw new RequiredArgumentMissingException('uuid'), $this->user->responseType));
    }
    private function default(): void
    {
        $this->setTextures(new DefaultType($this->user->responseType, $this->skin ? true : false, $this->cape ? true : false));
    }
    private function setTextures(StorageType|MojangType|CollectionType|DefaultType $storageType): void
    {
        null === $storageType->skinData ?: $this->skin = new Skin($this->textureStorageType, $storageType->skinData, $storageType->skinUrl, $storageType->skinSlim);
        null === $storageType->capeData ?: $this->cape = new Cape($this->textureStorageType, $storageType->capeData, $storageType->capeUrl);
    }
    /**
     * Если MethodTypeEnum не равен MOJANG, то список начинается сначала и идёт до конца,
     * пока не будут найдены текстуры
     *
     * @return void
     */
    private function nextTextureStorage()
    {
        if (
            ($this->skin === null || $this->cape === null) &&
            $this->user->methodType !== MethodTypeEnum::MOJANG &&
            $this->user->responseType === ResponseTypeEnum::JSON
        ) $this->textureStorageType = $this->textureStorageType->next();
    }
    private function generateTextureID(): void
    {
        /** @psalm-suppress TypeDoesNotContainType */
        [$this->skinID, $this->capeID] = match (Config::USER_STORAGE_TYPE) {
            UserStorageTypeEnum::USERNAME => [$this->user->username, $this->user->username],
            UserStorageTypeEnum::UUID => [$this->user->uuid, $this->user->uuid],
            UserStorageTypeEnum::DB_USER_ID => $this->getTextureIDFromDB(),
            UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => $this->getTextureHashFromDB()
        };
    }
    private function getTextureIDFromDB(): array
    {
        $MODULE_ARRAY_DATA = MainConfig::MODULES['TextureProvider'];
        $user_id_column = $MODULE_ARRAY_DATA['table_user']['id_column'];
        $table_user = $MODULE_ARRAY_DATA['table_user']['TABLE_NAME'];
        $uuid_column = $MODULE_ARRAY_DATA['table_user']['uuid_column'];
        /** @var int|string $user_id */
        $user_id = SingletonConnector::get('TextureProvider')->query(<<<SQL
            SELECT $user_id_column 
            FROM $table_user 
            WHERE $uuid_column = ?
        SQL, "s", $this->user->uuid)->value();
        return [(string)$user_id, (string)$user_id];
    }
    /** @return array{0: null|string, 1: null|string} */
    public function getTextureHashFromDB(): array
    {
        $skinID = null;
        $capeID = null;
        $MODULE_ARRAY_DATA = MainConfig::MODULES['TextureProvider'];

        $table_users = $MODULE_ARRAY_DATA['table_user']['TABLE_NAME'];
        $table_user_assets = $MODULE_ARRAY_DATA['table_user_assets']['TABLE_NAME'];

        $texture_type_column = $MODULE_ARRAY_DATA['table_user_assets']['texture_type_column'];
        $hash_column = $MODULE_ARRAY_DATA['table_user_assets']['hash_column'];
        $texture_meta_column = $MODULE_ARRAY_DATA['table_user_assets']['texture_meta_column'];

        $user_id_column = $MODULE_ARRAY_DATA['table_user']['id_column'];
        $assets_id_column = $MODULE_ARRAY_DATA['table_user_assets']['id_column'];
        $user_uuid_column = $MODULE_ARRAY_DATA['table_user']['uuid_column'];

        /** @var list<array<string, string>> $result */
        $result = SingletonConnector::get('TextureProvider')->query(<<<SQL
            SELECT $texture_type_column , $hash_column, $texture_meta_column
            FROM $table_user_assets as ASSETS
            INNER JOIN $table_users as USERS
            ON ASSETS.$assets_id_column = USERS.$user_id_column
            WHERE USERS.$user_uuid_column = ?
        SQL, "s", $this->user->uuid);
        foreach ($result as $v) {
            if (ResponseTypeEnum::SKIN === ResponseTypeEnum::tryFromString($v[$texture_type_column])) {
                $skinID = $v[$hash_column];
                // Need Add meta
            }
            if (ResponseTypeEnum::CAPE === ResponseTypeEnum::tryFromString($v[$texture_type_column]))
                $capeID = $v[$hash_column];
        }
        return [$skinID, $capeID];
    }
    public static function urlComplete(TextureStorageTypeEnum $textureStorageType, string $url): string
    {
        return match ($textureStorageType) {
            TextureStorageTypeEnum::MOJANG => $url,
            default => str_ends_with_slash(PathConfig::APP_URL) . Config::SCRIPT_URL . $url, // GET Params
        };
    }
    /**
     * JsonResponse::failed(error: throw new FileUploadException(UPLOAD_ERR_NO_FILE));
     *
     * @param RequestParams|LoaderRequestParams $requestParams
     * @param array{'tmp_name': string, 'error': int, 'size': int} $file tmp_name - Путь к временному файлу текстуры
     * @return Skin|Cape use JsonResponse::response($texture);
     */
    public static function loadTexture(RequestParams|LoaderRequestParams $requestParams, array $file): Skin|Cape
    {
        if (!in_array($requestParams->responseType, [ResponseTypeEnum::SKIN, ResponseTypeEnum::CAPE]))
            throw new \ValueError(sprintf(
                '%s может быть только: %s или %s',
                ResponseTypeEnum::getNameRequestVariable(),
                ResponseTypeEnum::SKIN->name,
                ResponseTypeEnum::CAPE->name
            ));
        $requestParams->withEnum(TextureStorageTypeEnum::STORAGE);
        /**
         * @var ResponseTypeEnum::SKIN|ResponseTypeEnum::CAPE $requestParams->responseType
         * @var string $requestParams->username
         * @var string $requestParams->uuid
         */
        $texturePathStorage = TextureUtils::getTexturePathStorage($requestParams->responseType->name);
        if (!is_dir($texturePathStorage)) {
            mkdir($texturePathStorage, 0666, true);
        }
        if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) throw new FileUploadException($file['error']);
        if ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) throw new FileUploadException(9);

        $data = file_get_contents($file['tmp_name']);

        (GDUtils::getImageMimeType($data) !== IMAGETYPE_PNG) ? throw new TextureLoaderException(PNG_FILE_SELECTION) : '';
        ($file['size'] <= TextureConfig::MAX_SIZE_BYTES) ?: throw new TextureLoaderException(FILE_SIZE_EXCEED);

        [$image, $w, $h] = GDUtils::pre_calculation($data);

        try {
            TextureUtils::validateHDSize($w, $h, $requestParams->responseType->name);
            if (Config::USE_LUCKPERMS_PERMISSION_HD_SKIN && (new LuckPerms($requestParams))->getUserWeight() < Config::MIN_WEIGHT || !TRUE) {
                match ($requestParams->responseType) {
                    ResponseTypeEnum::SKIN => throw new TextureLoaderException(NO_HD_SKIN_PERMISSION),
                    ResponseTypeEnum::CAPE => throw new TextureLoaderException(NO_HD_CAPE_PERMISSION),
                };
            }
        } catch (TextureSizeHDException) {
            TextureUtils::validateSize($w, $h, $requestParams->responseType->name);
        }
        $MODULE_ARRAY_DATA = MainConfig::MODULES['TextureProvider'];
        $table_users = $MODULE_ARRAY_DATA['table_user']['TABLE_NAME'];
        $user_id_column = $MODULE_ARRAY_DATA['table_user']['id_column'];
        $user_uuid_column = $MODULE_ARRAY_DATA['table_user']['uuid_column'];
        $user_id = '';
        if (in_array(Config::USER_STORAGE_TYPE, [UserStorageTypeEnum::DB_USER_ID, UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256])) {
            $user_id = (string)SingletonConnector::get('TextureProvider')->query(<<<SQL
                SELECT $user_id_column FROM $table_users WHERE $user_uuid_column IN (?)
                SQL, "s", $requestParams->uuid)->value();
        }
        /** @var string $requestParams->login */
        $requestParams->setVariable(
            'login',
            match (Config::USER_STORAGE_TYPE) {
                UserStorageTypeEnum::USERNAME => $requestParams->username,
                UserStorageTypeEnum::UUID => $requestParams->uuid,
                UserStorageTypeEnum::DB_USER_ID => $user_id,
                UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => TextureUtils::digest($data)
            }
        );
        /** @var bool $texture->isSlim */
        $texture = static::generateTextureFromLoaderRequestParams($requestParams, $data, $image);

        $filepath = TextureUtils::getTexturePath($requestParams->login, $requestParams->responseType->name);
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            //chmod($filepath, 0664);
            if (in_array(Config::USER_STORAGE_TYPE, [UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256])) {
                $meta_texture = (string)match ($requestParams->responseType) {
                    ResponseTypeEnum::SKIN => (int)$texture->isSlim,
                    ResponseTypeEnum::CAPE => 0
                };
                static::insertOrUpdateAssetDB($user_id, $requestParams->responseType->name, $requestParams->login, $meta_texture);
            }
            return $texture;
        } else throw new TextureLoaderException(FILE_MOVE_FAILED);
    }
    public static function generateTextureFromLoaderRequestParams(RequestParams|LoaderRequestParams $requestParams, string $data, \GdImage $gdImage): Skin|Cape
    {
        /** @var string $requestParams->login */
        return match ($requestParams->responseType) {
            ResponseTypeEnum::SKIN => new Skin(
                textureStorageType: TextureStorageTypeEnum::STORAGE,
                data: $data,
                url: (string) $requestParams,
                isSlim: GDUtils::checkSkinSlimFromImage($gdImage),
                digest: match (Config::USER_STORAGE_TYPE) {
                    UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => $requestParams->login,
                    default => null
                }
            ),
            ResponseTypeEnum::CAPE => new Cape(
                textureStorageType: TextureStorageTypeEnum::STORAGE,
                data: $data,
                url: (string) $requestParams,
                digest: match (Config::USER_STORAGE_TYPE) {
                    UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => $requestParams->login,
                    default => null
                }
            )
        };
    }
    public static function insertOrUpdateAssetDB(string $user_id, string $type, string $hash, string $meta_texture): void
    {
        $table_user_assets = MainConfig::MODULES['TextureProvider']['table_user_assets']['TABLE_NAME'];
        $assets_id_column = MainConfig::MODULES['TextureProvider']['table_user_assets']['id_column'];
        $texture_type_column = MainConfig::MODULES['TextureProvider']['table_user_assets']['texture_type_column'];
        $hash_column = MainConfig::MODULES['TextureProvider']['table_user_assets']['hash_column'];
        $texture_meta_column = MainConfig::MODULES['TextureProvider']['table_user_assets']['texture_meta_column'];
        SingletonConnector::get('TextureProvider')->query(
            <<<SQL
                INSERT INTO $table_user_assets ($assets_id_column, $texture_type_column, $hash_column, $texture_meta_column)
                VALUES (?, ?, ?, ?)
            SQL .
                match (MainConfig::DB_SUD_DB) {
                    SubDBTypeEnum::MySQL => <<<SQL
                ON DUPLICATE KEY UPDATE
                $hash_column = VALUES($hash_column), $texture_meta_column = VALUES($texture_meta_column)
            SQL,
                    SubDBTypeEnum::PostgreSQL => <<<SQL
                ON CONFLICT ($assets_id_column, $texture_type_column) DO UPDATE SET
                $hash_column = excluded.$hash_column, $texture_meta_column = excluded.$texture_meta_column
            SQL
                },
            "ssss",
            $user_id,
            $type,
            $hash,
            $meta_texture
        );
    }
    public function toArray(): array|stdClass
    {
        $json = [];
        if ($this->skin) $json['SKIN'] = $this->skin;
        if ($this->cape) $json['CAPE'] = $this->cape;
        // $json['AVATAR'] = [
        //     'url' => PathConfig::APP_URL . 'TextureReturner.php?method=avatar&size=42&login=' . $this->user->username,
        //     // 'digest' => ''
        // ];
        return !empty($json) ? $json : new stdClass;
    }
    public function jsonSerialize(): array|stdClass
    {
        return $this->toArray();
    }
    public static function ResponseTexture(string|null $data): never
    {
        if ($data == null) {
            http_response_code(404);
            exit;
        }
        header("Content-type: image/png");
        die($data);
    }
}
