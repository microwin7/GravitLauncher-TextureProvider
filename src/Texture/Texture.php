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
use Carbon\Carbon;
use JsonSerializable;
use Microwin7\PHPUtils\Main;
use Microwin7\TextureProvider\Config;
use Microwin7\PHPUtils\DB\SubDBTypeEnum;
use Microwin7\TextureProvider\Data\User;
use Microwin7\PHPUtils\Configs\MainConfig;
use Microwin7\PHPUtils\Helpers\FileSystem;
use Microwin7\TextureProvider\Texture\Cape;
use Microwin7\TextureProvider\Texture\Skin;
use Psr\Http\Message\UploadedFileInterface;
use Microwin7\TextureProvider\Utils\GDUtils;
use Microwin7\PHPUtils\DB\SingletonConnector;
use Symfony\Component\HttpFoundation\Request;
use Microwin7\TextureProvider\Utils\LuckPerms;
use Symfony\Component\HttpFoundation\Response;
use Microwin7\PHPUtils\Utils\Texture as TextureUtils;
use Microwin7\PHPUtils\Exceptions\FileSystemException;
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
    public              ?true                       $isSlim = null;
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
            Config::GIVE_FROM_COLLECTION() &&
            $this->skin === null &&
            $this->textureStorageType === TextureStorageTypeEnum::COLLECTION
        ) $this->collection();

        if ($this->textureStorageType === TextureStorageTypeEnum::COLLECTION) $this->nextTextureStorage();

        if (
            (Config::GIVE_DEFAULT_SKIN() || Config::GIVE_DEFAULT_CAPE()) &&
            $this->textureStorageType === TextureStorageTypeEnum::DEFAULT
        ) $this->default();
    }

    private function storage(): void
    {
        $this->setTextures(new StorageType($this->skinID, $this->isSlim, $this->capeID, $this->user->responseType));
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
        [$this->skinID, $this->capeID] = match (Config::USER_STORAGE_TYPE()) {
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
            SELECT ASSETS.$texture_type_column, ASSETS.$hash_column, ASSETS.$texture_meta_column
            FROM $table_user_assets as ASSETS
            INNER JOIN $table_users as USERS
            ON ASSETS.$assets_id_column = USERS.$user_id_column
            WHERE USERS.$user_uuid_column = ?
        SQL, "s", $this->user->uuid);
        foreach ($result as $v) {
            if (ResponseTypeEnum::SKIN === ResponseTypeEnum::tryFromString($v[$texture_type_column])) {
                $skinID = $v[$hash_column];
                if ($v[$texture_meta_column] === 'SLIM') $this->isSlim = true;
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
            default => Main::getScriptURL() . $url, // GET Params
        };
    }
    public static function getSkinDataForAvatar(string|null $login): string
    {
        $storageType = new StorageType($login, null, null, ResponseTypeEnum::AVATAR);
        if ($storageType->skinData !== null) return $storageType->skinData;
        $storageType = new DefaultType(ResponseTypeEnum::AVATAR, false, true);
        if ($storageType->skinData !== null) return $storageType->skinData;
        else self::ResponseTexture(null);
    }
    public static function loadTexture(RequestParams|LoaderRequestParams $requestParams, UploadedFileInterface $uploadedFile, bool $hdAllow = false): Skin|Cape
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
        $texturePathStorage = TextureUtils::TEXTURE_STORAGE_FULL_PATH($requestParams->responseType);
        if (!is_dir($texturePathStorage)) FileSystem::mkdir($texturePathStorage);
        $uploadedFile->getError() === UPLOAD_ERR_OK ?: throw new FileUploadException($uploadedFile->getError());
        $uploadedFile->getSize() <= TextureUtils::MAX_SIZE_BYTES() ?: throw new TextureLoaderException(FILE_SIZE_EXCEED);
        try {
            $data = $uploadedFile->getStream()->getContents();
        } catch (\RuntimeException) {
            throw new FileUploadException(9);
        }
        GDUtils::getImageMimeType($data) === IMAGETYPE_PNG ?: throw new TextureLoaderException(PNG_FILE_SELECTION);

        [$image, $w, $h] = GDUtils::pre_calculation($data);

        try {
            TextureUtils::validateHDSize($w, $h, $requestParams->responseType);
            if ((Config::LUCKPERMS_USE_PERMISSION_HD_SKIN() && (new LuckPerms($requestParams))->getUserWeight() < Config::LUCKPERMS_MIN_WEIGHT()) && !$hdAllow) {
                match ($requestParams->responseType) {
                    ResponseTypeEnum::SKIN => throw new TextureLoaderException(NO_HD_SKIN_PERMISSION),
                    ResponseTypeEnum::CAPE => throw new TextureLoaderException(NO_HD_CAPE_PERMISSION),
                };
            }
        } catch (TextureSizeHDException) {
            TextureUtils::validateSize($w, $h, $requestParams->responseType);
        }
        $MODULE_ARRAY_DATA = MainConfig::MODULES['TextureProvider'];
        $table_users = $MODULE_ARRAY_DATA['table_user']['TABLE_NAME'];
        $user_id_column = $MODULE_ARRAY_DATA['table_user']['id_column'];
        $user_uuid_column = $MODULE_ARRAY_DATA['table_user']['uuid_column'];
        $user_id = '';
        if (in_array(Config::USER_STORAGE_TYPE(), [UserStorageTypeEnum::DB_USER_ID, UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256])) {
            $user_id = (string)SingletonConnector::get('TextureProvider')->query(<<<SQL
                SELECT $user_id_column FROM $table_users WHERE $user_uuid_column IN (?)
                SQL, "s", $requestParams->uuid)->value();
        }
        /** @var string $requestParams->login */
        $requestParams->setVariable(
            'login',
            match (Config::USER_STORAGE_TYPE()) {
                UserStorageTypeEnum::USERNAME => $requestParams->username,
                UserStorageTypeEnum::UUID => $requestParams->uuid,
                UserStorageTypeEnum::DB_USER_ID => $user_id,
                UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => TextureUtils::digest($data)
            }
        );
        /** @var bool $texture->isSlim */
        $texture = static::generateTextureFromLoaderRequestParams($requestParams, $data, $image);

        try {
            $uploadedFile->moveTo(TextureUtils::PATH($requestParams->responseType, $requestParams->login));
        } catch (\RuntimeException $e) {
            throw new FileSystemException(FILE_MOVE_FAILED . $e->getMessage());
        }
        if (in_array(Config::USER_STORAGE_TYPE(), [UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256])) {
            static::insertOrUpdateAssetDB(
                $user_id,
                $requestParams->responseType->name,
                $requestParams->login,
                (string)match ($requestParams->responseType) {
                    ResponseTypeEnum::SKIN => (int)$texture->isSlim,
                    ResponseTypeEnum::CAPE => 0
                }
            );
        }
        return $texture;
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
                digest: match (Config::USER_STORAGE_TYPE()) {
                    UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => $requestParams->login,
                    default => null
                }
            ),
            ResponseTypeEnum::CAPE => new Cape(
                textureStorageType: TextureStorageTypeEnum::STORAGE,
                data: $data,
                url: (string) $requestParams,
                digest: match (Config::USER_STORAGE_TYPE()) {
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
                match (Main::DB_SUD_DB()) {
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
        if ($this->skin) {
            $json[ResponseTypeEnum::SKIN->name] = $this->skin;
            if ($this->textureStorageType !== TextureStorageTypeEnum::MOJANG) {
                $json[ResponseTypeEnum::AVATAR->name] = [
                    'url' => Main::getScriptURL() . match (Config::ROUTERING()) {
                        TRUE => implode('/', array_filter(
                            [
                                ResponseTypeEnum::AVATAR->name,
                                Config::AVATAR_CANVAS() ?? Config::BLOCK_CANVAS(),
                                $this->skinID ?? 'DEFAULT'
                            ],
                            function ($v) {
                                return $v !== null;
                            }
                        )),
                        FALSE => 'index.php?' . http_build_query(
                            [
                                ResponseTypeEnum::getNameRequestVariable() => ResponseTypeEnum::AVATAR->name,
                                'size' => Config::AVATAR_CANVAS() ?? Config::BLOCK_CANVAS(),
                                'login' => $this->skinID ?? 'DEFAULT',
                            ]
                        ),
                    },
                ];
                $json[ResponseTypeEnum::FRONT->name] = [
                    'url' => Main::getScriptURL() . match (Config::ROUTERING()) {
                        TRUE => implode('/', array_filter(
                            [
                                ResponseTypeEnum::FRONT->name,
                                Config::BLOCK_CANVAS(),
                                $this->skinID ?? 'DEFAULT'
                            ],
                            function ($v) {
                                return $v !== null;
                            }
                        )),
                        FALSE => 'index.php?' . http_build_query(
                            [
                                ResponseTypeEnum::getNameRequestVariable() => ResponseTypeEnum::FRONT->name,
                                'size' => Config::BLOCK_CANVAS(),
                                'login' => $this->skinID ?? 'DEFAULT',
                            ]
                        ),
                    },
                ];
            }
        }
        if ($this->cape) $json[ResponseTypeEnum::CAPE->name] = $this->cape;
        return !empty($json) ? $json : new stdClass;
    }
    public function jsonSerialize(): array|stdClass
    {
        return $this->toArray();
    }
    public static function ResponseTexture(string|null $CONTENT, int|null $lastModified = null): never
    {
        if ($CONTENT == null) {
            http_response_code(404);
            exit;
        }
        $request = Request::createFromGlobals();
        $response = new Response();
        $max_age = Config::IMAGE_CACHE_TIME();
        if ($max_age === null) {
            $max_age = match (Config::USER_STORAGE_TYPE()) {
                UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => 604800,
                default => 60
            };
        }
        // Установка заголовков
        $response->headers->set('Content-Type', 'image/png');
        $response->headers->set('Content-Length', (string) strlen($CONTENT));
        $response->setLastModified(Carbon::createFromTimestamp($lastModified ?? time()));
        $response->setETag(hash('sha256', $CONTENT));
        $response->setPublic();
        $response->setMaxAge($max_age);
        match (Config::USER_STORAGE_TYPE()) {
            UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => $response->setImmutable(),
            default => null
        };
        $response->headers->set('Expires', Carbon::now('UTC')->addSeconds($max_age)->toRfc7231String());
        $response->headers->set('Accept-Ranges', 'bytes');
        // Проверка, изменился ли ресурс
        if ($response->isNotModified($request)) {
            $response->setNotModified();
        } else {
            $response->setContent($CONTENT);
        }
        // Отправка ответа
        $response->send();
        exit;
    }
}
