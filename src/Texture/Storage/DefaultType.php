<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture\Storage;

use TypeError;
use Microwin7\TextureProvider\Utils\GDUtils;
use Microwin7\PHPUtils\Configs\TextureConfig;
use Microwin7\TextureProvider\Configs\Config;
use Microwin7\TextureProvider\Texture\Texture;
use Microwin7\TextureProvider\Utils\RequestParams;
use Microwin7\TextureProvider\Data\ResponseTypeEnum;
use Microwin7\TextureProvider\Texture\TextureStorageTypeEnum;

class DefaultType
{
    public          ?string             $skinData = null;
    public readonly RequestParams       $skinUrl;
    public readonly bool                $skinSlim;

    public          ?string             $capeData = null;
    public readonly RequestParams       $capeUrl;

    function __construct(
                    ResponseTypeEnum    $responseType,
                    bool                $skinAlreadyDetected,
                    bool                $capeAlreadyDetected
    ) {
        if (Config::GIVE_DEFAULT_SKIN && $responseType !== ResponseTypeEnum::CAPE && $skinAlreadyDetected === false) {
            if (!is_null($this->skinData = $this->getSkinData())) {
                $this->skinResize();
                if ($responseType === ResponseTypeEnum::SKIN) Texture::ResponseTexture($this->skinData);
                $this->skinUrl = $this->getSkinUrl();
                $this->skinSlim = $this->checkIsSlim();
            }
        }
        if (Config::GIVE_DEFAULT_CAPE && $responseType !== ResponseTypeEnum::SKIN && $capeAlreadyDetected === false) {
            if (!is_null($this->capeData = $this->getCapeData())) {
                if ($responseType === ResponseTypeEnum::CAPE) Texture::ResponseTexture($this->capeData);
                $this->capeUrl = $this->getCapeUrl();
            }
        }
    }
    private function getSkinData(): ?string
    {
        return base64_decode(TextureConfig::SKIN_DEFAULT) ?: null;
    }
    private function skinResize(): void
    {
        if (Config::SKIN_RESIZE && $this->skinData !== null) {
            try {
                $this->skinData = GDUtils::skin_resize($this->skinData);
            } catch (TypeError $e) {
                throw new TypeError(sprintf(
                    '%s' . PHP_EOL . '%s',
                    $e->getMessage(),
                    'StorageType: ' . __CLASS__,
                ));
            }
        }
    }
    private function getSkinUrl(): RequestParams
    {
        return new RequestParams(ResponseTypeEnum::SKIN, TextureStorageTypeEnum::DEFAULT);
    }
    private function checkIsSlim(): bool
    {
        try {
            return GDUtils::slim($this->skinData);
        } catch (TypeError $e) {
            throw new TypeError(sprintf(
                '%s' . PHP_EOL . '%s',
                $e->getMessage(),
                'StorageType: ' . __CLASS__,
            ));
        }
    }
    private function getCapeData(): ?string
    {
        return base64_decode(TextureConfig::CAPE_DEFAULT) ?: null;
    }
    private function getCapeUrl(): RequestParams
    {
        return new RequestParams(ResponseTypeEnum::CAPE, TextureStorageTypeEnum::DEFAULT);
    }
}
