<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture\Storage;

use TypeError;
use Microwin7\TextureProvider\Config;
use Microwin7\PHPUtils\Helpers\FileSystem;
use Microwin7\TextureProvider\Utils\Cache;
use Microwin7\TextureProvider\Utils\GDUtils;
use Microwin7\TextureProvider\Texture\Texture;
use Microwin7\PHPUtils\Utils\Texture as UtilsTexture;
use Microwin7\PHPUtils\Exceptions\FileSystemException;
use Microwin7\PHPUtils\Contracts\User\UserStorageTypeEnum;
use Microwin7\TextureProvider\Request\Provider\RequestParams;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\PHPUtils\Contracts\Texture\Enum\TextureStorageTypeEnum;

class StorageType
{
    public              ?string             $skinData = null;
    public              ?int                $skinLastModified = null;
    /** @psalm-suppress PropertyNotSetInConstructor */
    public readonly     string              $skinUrl;
    /** @psalm-suppress PropertyNotSetInConstructor */
    public readonly     bool                $skinSlim;

    public              ?string             $capeData = null;
    public              ?int                $capeLastModified = null;
    /** @psalm-suppress PropertyNotSetInConstructor */
    public readonly     string              $capeUrl;

    private readonly    FileSystem          $fileSystem;

    function __construct(
        public          ?string             $skinID,
        public          ?true               $isSlim,
        public          ?string             $capeID,
        ResponseTypeEnum    $responseType
    ) {
        $this->fileSystem = new FileSystem;
        if ($this->skinID !== null && in_array($responseType, [ResponseTypeEnum::JSON, ResponseTypeEnum::SKIN, ResponseTypeEnum::AVATAR])) {
            $this->getSkinData();
            if ($this->skinData !== null) {
                if ($responseType !== ResponseTypeEnum::JSON && $responseType !== ResponseTypeEnum::AVATAR) $this->skinResize();
                if ($responseType === ResponseTypeEnum::SKIN) Texture::ResponseTexture($this->skinData, $this->skinLastModified);
                if ($responseType !== ResponseTypeEnum::AVATAR) {
                    $this->skinUrl = $this->getSkinUrl($responseType);
                    $this->skinSlim = $this->checkIsSlim();
                }
            }
        }
        if ($this->capeID !== null && in_array($responseType, [ResponseTypeEnum::JSON, ResponseTypeEnum::CAPE])) {
            $this->getCapeData();
            if ($this->capeData !== null) {
                if ($responseType === ResponseTypeEnum::CAPE) Texture::ResponseTexture($this->capeData, $this->capeLastModified);
                $this->capeUrl = $this->getCapeUrl();
            }
        }
    }
    private function getSkinData(): void
    {
        /** @var string $this->skinID */
        if (Config::USER_STORAGE_TYPE() === UserStorageTypeEnum::USERNAME) {
            if (!$this->fileSystem->is_file($skinPath = UtilsTexture::PATH(ResponseTypeEnum::SKIN, $this->skinID))) {
                $textureSkinStorage = UtilsTexture::TEXTURE_STORAGE_FULL_PATH(ResponseTypeEnum::SKIN);
                try {
                    $username = $this->fileSystem->findFile($textureSkinStorage, $this->skinID, UtilsTexture::EXTENSTION());
                } catch (FileSystemException $e) {
                    if ($e->isErrorFolderNotExist()) FileSystem::mkdir($textureSkinStorage);
                    $username = null;
                }
                if ($username !== null) {
                    $filename = UtilsTexture::PATH(ResponseTypeEnum::SKIN, $this->skinID = $username);
                    $this->skinData = file_get_contents($filename);
                    $this->skinLastModified = Cache::getLastModified($filename);
                } else {
                    // NULL
                }
            } else {
                $this->skinData = file_get_contents($skinPath);
                $this->skinLastModified = Cache::getLastModified($skinPath);
            }
        } else {
            if ($this->fileSystem->is_file($skinPath = UtilsTexture::PATH(ResponseTypeEnum::SKIN, $this->skinID))) {
                $this->skinData = file_get_contents($skinPath);
                $this->skinLastModified = Cache::getLastModified($skinPath);
            }
        }
    }
    private function skinResize(): void
    {
        /**
         * @var string $this->skinData
         * @var string $this->skinID
         */
        if (Config::SKIN_RESIZE()) {
            try {
                $this->skinData = GDUtils::skin_resize($this->skinData);
            } catch (TypeError $e) {
                throw new TypeError(sprintf(
                    '%s' . PHP_EOL . '%s' . PHP_EOL . '%s',
                    $e->getMessage(),
                    'StorageType: ' . __CLASS__,
                    'With skinID: ' . $this->skinID,
                ));
            }
        }
    }
    private function getSkinUrl(ResponseTypeEnum $responseType): string
    {
        $requestParams = new RequestParams;
        $requestParams
            ->withEnum($responseType === ResponseTypeEnum::JSON ? ResponseTypeEnum::SKIN : $responseType)
            ->withEnum(TextureStorageTypeEnum::STORAGE)
            ->setVariable('login', $this->skinID);
        return (string)$requestParams;
    }
    private function checkIsSlim(): bool
    {
        /**
         * @var string $this->skinData
         * @var string $this->skinID
         */
        if ($this->isSlim) return $this->isSlim;
        try {
            return GDUtils::slim($this->skinData);
        } catch (TypeError $e) {
            throw new TypeError(sprintf(
                '%s' . PHP_EOL . '%s' . PHP_EOL . '%s',
                $e->getMessage(),
                'StorageType: ' . __CLASS__,
                'With skinID: ' . $this->skinID,
            ));
        }
    }
    private function getCapeData(): void
    {
        /** @var string $this->capeID */
        if (Config::USER_STORAGE_TYPE() === UserStorageTypeEnum::USERNAME) {
            if (!$this->fileSystem->is_file($capePath = UtilsTexture::PATH(ResponseTypeEnum::CAPE, $this->capeID))) {
                $textureCapeStorage = UtilsTexture::TEXTURE_STORAGE_FULL_PATH(ResponseTypeEnum::CAPE);
                try {
                    $username = $this->fileSystem->findFile($textureCapeStorage, $this->capeID, UtilsTexture::EXTENSTION());
                } catch (FileSystemException $e) {
                    if ($e->isErrorFolderNotExist()) FileSystem::mkdir($textureCapeStorage);
                    $username = null;
                }
                if ($username !== null) {
                    $filename = UtilsTexture::PATH(ResponseTypeEnum::CAPE, $this->capeID = $username);
                    $this->capeData = file_get_contents($filename);
                    $this->capeLastModified = Cache::getLastModified($filename);
                } else {
                    // NULL
                }
            } else {
                $this->capeData = file_get_contents($capePath);
                $this->capeLastModified = Cache::getLastModified($capePath);
            }
        } else {
            if ($this->fileSystem->is_file($capePath = UtilsTexture::PATH(ResponseTypeEnum::CAPE, $this->capeID))) {
                $this->capeData = file_get_contents($capePath);
                $this->capeLastModified = Cache::getLastModified($capePath);
            }
        }
    }
    private function getCapeUrl(): string
    {
        $requestParams = new RequestParams;
        $requestParams
            ->withEnum(ResponseTypeEnum::CAPE)
            ->withEnum(TextureStorageTypeEnum::STORAGE)
            ->setVariable('login', $this->capeID);
        return (string)$requestParams;
    }
}
