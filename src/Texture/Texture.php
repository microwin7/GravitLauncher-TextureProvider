<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture;

use stdClass;
use JsonSerializable;
use Microwin7\PHPUtils\DB\Connector;
use Microwin7\PHPUtils\DB\DriverPDO;
use Microwin7\PHPUtils\DB\DriverMySQLi;
use Microwin7\TextureProvider\Data\User;
use Microwin7\PHPUtils\Configs\MainConfig;
use Microwin7\PHPUtils\Configs\PathConfig;
use Microwin7\TextureProvider\Texture\Cape;
use Microwin7\TextureProvider\Texture\Skin;
use Microwin7\TextureProvider\Configs\Config;
use Microwin7\TextureProvider\Data\MethodTypeEnum;
use Microwin7\TextureProvider\Utils\RequestParams;
use function Microwin7\PHPUtils\str_ends_with_slash;
use Microwin7\TextureProvider\Data\ResponseTypeEnum;
use Microwin7\TextureProvider\Data\UserStorageTypeEnum;
use Microwin7\TextureProvider\Texture\Storage\MojangType;
use Microwin7\TextureProvider\Texture\Storage\DefaultType;
use Microwin7\TextureProvider\Texture\Storage\StorageType;
use Microwin7\TextureProvider\Texture\Storage\CollectionType;

class Texture implements JsonSerializable
{
    public              ?Skin                       $skin = null;
    public              ?Cape                       $cape = null;
    public              ?string                     $skinID = null;
    public              ?string                     $capeID = null;

    private             TextureStorageTypeEnum      $textureStorageType;
    private readonly    StorageType                 $storageType;
    private readonly    MojangType                  $mojangType;
    private readonly    CollectionType              $collectionType;
    private readonly    DefaultType                 $defaultType;

    private readonly    DriverPDO|DriverMySQLi      $DB;

    public function __construct(
        public          User                        $user,
                        DriverPDO|DriverMySQLi|null $DB = null // Only TextureProvider Module
    ) {
        $this->textureStorageType = $this->user->textureStorageType;
        if (
            $this->user->responseType === ResponseTypeEnum::JSON &&
            (Config::USER_STORAGE_TYPE === UserStorageTypeEnum::DB_USER_ID ||
                Config::USER_STORAGE_TYPE === UserStorageTypeEnum::DB_SHA1 ||
                Config::USER_STORAGE_TYPE === UserStorageTypeEnum::DB_SHA256)
        ) $this->DB = null === $DB ? (new Connector)->{'TextureProvider'} : $DB;
        if ($this->textureStorageType === TextureStorageTypeEnum::STORAGE) {
            if ($this->user->responseType === ResponseTypeEnum::JSON) $this->generateTextureID();
            if ($this->user->responseType === ResponseTypeEnum::SKIN) $this->skinID = $this->user->login;
            if ($this->user->responseType === ResponseTypeEnum::CAPE) $this->capeID = $this->user->login;
        }
        $this->findData();
        // RESPONSE 404 null texture
        if ($this->user->responseType !== ResponseTypeEnum::JSON) $this->ResponseTexture(null);
    }

    private function findData()
    {
        if (
            $this->textureStorageType === TextureStorageTypeEnum::STORAGE &&
            $this->user->methodType !== MethodTypeEnum::MOJANG
        ) $this->storage();

        if ($this->textureStorageType === TextureStorageTypeEnum::STORAGE) $this->nextTextureStorage();

        if (
            $this->user->responseType === ResponseTypeEnum::JSON &&
            $this->textureStorageType === TextureStorageTypeEnum::MOJANG &&
            ($this->user->methodType === MethodTypeEnum::MOJANG || $this->user->methodType === MethodTypeEnum::HYBRID)
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

    private function storage()
    {
        $this->storageType = new StorageType($this->skinID, $this->capeID, $this->user->responseType);
        null === $this->storageType->skinData ?: $this->skin = new Skin($this->textureStorageType, $this->storageType->skinData, $this->storageType->skinUrl, $this->storageType->skinSlim);
        null === $this->storageType->capeData ?: $this->cape = new Cape($this->textureStorageType, $this->storageType->capeData, $this->storageType->capeUrl);
    }
    private function mojang()
    {
        $this->mojangType = new MojangType($this->user->username, $this->skin ? true : false, $this->cape ? true : false);
        null === $this->mojangType->skinData ?: $this->skin = new Skin($this->textureStorageType, $this->mojangType->skinData, $this->mojangType->skinUrl, $this->mojangType->skinSlim);
        null === $this->mojangType->capeData ?: $this->cape = new Cape($this->textureStorageType, $this->mojangType->capeData, $this->mojangType->capeUrl);
    }
    private function collection()
    {
        $this->collectionType = new CollectionType($this->user->uuid, $this->user->responseType);
        null === $this->collectionType->skinData ?: $this->skin = new Skin($this->textureStorageType, $this->collectionType->skinData, $this->collectionType->skinUrl, $this->collectionType->skinSlim);
    }
    private function default()
    {
        $this->defaultType = new DefaultType($this->user->responseType, $this->skin ? true : false, $this->cape ? true : false);
        null === $this->defaultType->skinData ?: $this->skin = new Skin($this->textureStorageType, $this->defaultType->skinData, $this->defaultType->skinUrl, $this->defaultType->skinSlim);
        null === $this->defaultType->capeData ?: $this->cape = new Cape($this->textureStorageType, $this->defaultType->capeData, $this->defaultType->capeUrl);
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
        [$this->skinID, $this->capeID] = match (Config::USER_STORAGE_TYPE) {
            UserStorageTypeEnum::USERNAME => [$this->user->username, $this->user->username],
            UserStorageTypeEnum::UUID => [$this->user->uuid, $this->user->uuid],
            UserStorageTypeEnum::DB_USER_ID => $this->getTextureIDFromDB(),
            UserStorageTypeEnum::DB_SHA1, UserStorageTypeEnum::DB_SHA256 => $this->getTextureHashFromDB()
        };
    }
    private function getTextureIDFromDB(): array|null
    {
        $user_id = $this->DB->query("SELECT " .
            MainConfig::MODULES['TextureProvider'][UserStorageTypeEnum::DB_USER_ID->name]
            . " FROM " .
            MainConfig::MODULES['TextureProvider']['table_user']
            . " WHERE " .
            MainConfig::MODULES['TextureProvider']['uuid_column'] .
            " = ?", "s", $this->user->uuid)->value();
        return [(string)$user_id, (string)$user_id];
    }
    public function getTextureHashFromDB(): array|null
    {
        $skinID = '';
        $capeID = '';
        foreach ($this->DB->query("SELECT " .
            MainConfig::MODULES['TextureProvider']['texture_type_column'] . ", "
            .
            MainConfig::MODULES['TextureProvider']['hash_column']
            . " FROM " .
            MainConfig::MODULES['TextureProvider']['table_user_assets']
            . " WHERE " .
            MainConfig::MODULES['TextureProvider']['uuid_column'] .
            " = ?", "s", $this->user->uuid)->array() as $v) {
            if (ResponseTypeEnum::SKIN === ResponseTypeEnum::tryFromString($v[MainConfig::MODULES['TextureProvider']['texture_type_column']]))
                $skinID = $v[MainConfig::MODULES['TextureProvider']['hash_column']];
            if (ResponseTypeEnum::CAPE === ResponseTypeEnum::tryFromString($v[MainConfig::MODULES['TextureProvider']['texture_type_column']]))
                $capeID = $v[MainConfig::MODULES['TextureProvider']['hash_column']];
        }
        return [$skinID, $capeID];
    }
    public static function urlComplete(TextureStorageTypeEnum $textureStorageType, string|RequestParams $url): string
    {
        return match ($textureStorageType) {
            TextureStorageTypeEnum::MOJANG => $url,
            default => str_ends_with_slash(PathConfig::APP_URL) . Config::SCRIPT_URL . $url, // GET Params
        };
    }
    public static function digest($data): string
    {
        return hash('sha256', $data);
    }
    public function toArray(): array|stdClass
    {
        $json = [];
        if ($this->skin) $json['SKIN'] = $this->skin;
        if ($this->cape) $json['CAPE'] = $this->cape;
        return !empty($json) ? $json : new stdClass;
    }
    public function jsonSerialize(): array|stdClass
    {
        return $this->toArray();
    }
    public static function ResponseTexture(string|null $data)
    {
        if ($data == null) {
            http_response_code(404);
            exit;
        }
        header("Content-type: image/png");
        die($data);
    }
}
