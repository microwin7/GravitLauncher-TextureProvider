<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture;

use stdClass;
use JsonSerializable;
use Microwin7\TextureProvider\Config;
use Microwin7\TextureProvider\Data\User;
use Microwin7\PHPUtils\Configs\MainConfig;
use Microwin7\PHPUtils\Configs\PathConfig;
use Microwin7\TextureProvider\Texture\Cape;
use Microwin7\TextureProvider\Texture\Skin;
use Microwin7\PHPUtils\DB\SingletonConnector;
use function Microwin7\PHPUtils\str_ends_with_slash;
use Microwin7\TextureProvider\Texture\Storage\MojangType;
use Microwin7\PHPUtils\Contracts\User\UserStorageTypeEnum;
use Microwin7\TextureProvider\Texture\Storage\DefaultType;
use Microwin7\TextureProvider\Texture\Storage\StorageType;
use Microwin7\PHPUtils\Contracts\Texture\Enum\MethodTypeEnum;
use Microwin7\TextureProvider\Texture\Storage\CollectionType;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\PHPUtils\Exceptions\RequiredArgumentMissingException;
use Microwin7\PHPUtils\Contracts\Texture\Enum\TextureStorageTypeEnum;

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
            if ($this->user->responseType === ResponseTypeEnum::JSON) $this->generateTextureID();
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
    public function toArray(): array|stdClass
    {
        $json = [];
        if ($this->skin) $json['SKIN'] = $this->skin;
        if ($this->cape) $json['CAPE'] = $this->cape;
        // $json['AVATAR'] = [
        //     'url' => 'http://127.0.0.1/TextureReturner.php?method=avatar&size=42&login=' . $this->user->username,
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
