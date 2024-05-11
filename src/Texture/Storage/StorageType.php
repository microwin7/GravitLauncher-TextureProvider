<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture\Storage;

use TypeError;
use Microwin7\TextureProvider\Config;
use Microwin7\PHPUtils\Helpers\FileSystem;
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
    public readonly     string              $skinUrl;
    public readonly     bool                $skinSlim;

    public              ?string             $capeData = null;
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
            if (!is_null($this->skinData = $this->getSkinData())) {
                if ($responseType !== ResponseTypeEnum::JSON && $responseType !== ResponseTypeEnum::AVATAR) $this->skinResize();
                if ($responseType === ResponseTypeEnum::SKIN) Texture::ResponseTexture($this->skinData);
                if ($responseType !== ResponseTypeEnum::AVATAR) {
                    $this->skinUrl = $this->getSkinUrl($responseType);
                    $this->skinSlim = $this->checkIsSlim();
                }
            }
        }
        if ($this->capeID !== null && in_array($responseType, [ResponseTypeEnum::JSON, ResponseTypeEnum::CAPE])) {
            if (!is_null($this->capeData = $this->getCapeData())) {
                if ($responseType === ResponseTypeEnum::CAPE) Texture::ResponseTexture($this->capeData);
                $this->capeUrl = $this->getCapeUrl();
            }
        }
    }
    private function getSkinData(): ?string
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
                    return file_get_contents(UtilsTexture::PATH(ResponseTypeEnum::SKIN, $this->skinID = $username));
                } else {
                    return null;
                }
            } else return file_get_contents($skinPath);
        } else {
            return $this->fileSystem->is_file($skinPath = UtilsTexture::PATH(ResponseTypeEnum::SKIN, $this->skinID)) ?
                file_get_contents($skinPath) : null;
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
    private function getCapeData(): ?string
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
                    return file_get_contents(UtilsTexture::PATH(ResponseTypeEnum::CAPE, $this->capeID = $username));
                } else {
                    return $username;
                }
            } else return file_get_contents($capePath);
        } else {
            return $this->fileSystem->is_file($capePath = UtilsTexture::PATH(ResponseTypeEnum::CAPE, $this->capeID)) ?
                file_get_contents($capePath) : null;
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
