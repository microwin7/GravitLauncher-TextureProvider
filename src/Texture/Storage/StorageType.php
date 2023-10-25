<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture\Storage;

use TypeError;
use Microwin7\TextureProvider\Utils\GDUtils;
use Microwin7\PHPUtils\Configs\TextureConfig;
use Microwin7\TextureProvider\Configs\Config;
use Microwin7\TextureProvider\Texture\Texture;
use Microwin7\TextureProvider\Helpers\FileSystem;
use Microwin7\TextureProvider\Utils\RequestParams;
use Microwin7\TextureProvider\Data\ResponseTypeEnum;
use Microwin7\PHPUtils\Utils\Texture as UtilsTexture;
use Microwin7\TextureProvider\Data\UserStorageTypeEnum;
use Microwin7\TextureProvider\Texture\TextureStorageTypeEnum;

class StorageType
{
    public              ?string             $skinData = null;
    public readonly     RequestParams       $skinUrl;
    public readonly     bool                $skinSlim;

    public              ?string             $capeData = null;
    public readonly     RequestParams       $capeUrl;

    private readonly    FileSystem          $fileSystem;

    function __construct(
        public          ?string             $skinID,
        public          ?string             $capeID,
                        ResponseTypeEnum    $responseType
    ) {
        $this->fileSystem = new FileSystem;
        if ($this->skinID !== null && $responseType !== ResponseTypeEnum::CAPE) {
            if (!is_null($this->skinData = $this->getSkinData())) {
                $this->skinResize();
                if ($responseType === ResponseTypeEnum::SKIN) Texture::ResponseTexture($this->skinData);
                $this->skinUrl = $this->getSkinUrl();
                $this->skinSlim = $this->checkIsSlim();
            }
        }
        if ($this->capeID !== null && $responseType !== ResponseTypeEnum::SKIN) {
            if (!is_null($this->capeData = $this->getCapeData())) {
                if ($responseType === ResponseTypeEnum::CAPE) Texture::ResponseTexture($this->capeData);
                $this->capeUrl = $this->getCapeUrl();
            }
        }
    }
    private function getSkinData(): ?string
    {
        if (Config::USER_STORAGE_TYPE === UserStorageTypeEnum::USERNAME) {
            if (!$this->fileSystem->is_file($skinPath = UtilsTexture::getSkinPath($this->skinID))) {
                $username = $this->fileSystem->findFile(UtilsTexture::getSkinPathStorage(), $this->skinID, TextureConfig::EXT);
                if ($username) {
                    return file_get_contents(UtilsTexture::getSkinPath($this->skinID = $username));
                } else {
                    return null;
                }
            } else return file_get_contents($skinPath);
        } else {
            return $this->fileSystem->is_file($skinPath = UtilsTexture::getSkinPath($this->skinID)) ?
                file_get_contents($skinPath) : null;
        }
    }
    private function skinResize(): void
    {
        if (Config::SKIN_RESIZE && $this->skinData !== null) {
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
    private function getSkinUrl(): RequestParams
    {
        return new RequestParams(ResponseTypeEnum::SKIN, TextureStorageTypeEnum::STORAGE, $this->skinID);
    }
    private function checkIsSlim(): bool
    {
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
        if (Config::USER_STORAGE_TYPE === UserStorageTypeEnum::USERNAME) {
            if (!$this->fileSystem->is_file($capePath = UtilsTexture::getCapePath($this->capeID))) {
                $username = $this->fileSystem->findFile(UtilsTexture::getCapePathStorage(), $this->capeID, TextureConfig::EXT);
                if ($username) {
                    return file_get_contents(UtilsTexture::getCapePath($this->capeID = $username));
                } else {
                    return null;
                }
            } else return file_get_contents($capePath);
        } else {
            return $this->fileSystem->is_file($capePath = UtilsTexture::getCapePath($this->capeID)) ?
                file_get_contents($capePath) : null;
        }
    }
    private function getCapeUrl(): RequestParams
    {
        return new RequestParams(ResponseTypeEnum::CAPE, TextureStorageTypeEnum::STORAGE, $this->capeID);
    }
}
