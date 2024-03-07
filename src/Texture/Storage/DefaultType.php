<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture\Storage;

use TypeError;
use Microwin7\TextureProvider\Config;
use Microwin7\TextureProvider\Utils\GDUtils;
use Microwin7\PHPUtils\Configs\TextureConfig;
use Microwin7\TextureProvider\Texture\Texture;
use Microwin7\TextureProvider\Request\Provider\RequestParams;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\PHPUtils\Contracts\Texture\Enum\TextureStorageTypeEnum;

class DefaultType
{
    public          ?string             $skinData = null;
    public readonly string              $skinUrl;
    public readonly bool                $skinSlim;

    public          ?string             $capeData = null;
    public readonly string              $capeUrl;

    function __construct(
                    ResponseTypeEnum    $responseType,
                    bool                $skinAlreadyDetected,
                    bool                $capeAlreadyDetected
    ) {
        if ($skinAlreadyDetected === false && Config::GIVE_DEFAULT_SKIN && in_array($responseType, [ResponseTypeEnum::JSON, ResponseTypeEnum::SKIN, ResponseTypeEnum::AVATAR])) {
            if (!is_null($this->skinData = $this->getSkinData())) {
                $this->skinResize();
                if ($responseType === ResponseTypeEnum::SKIN) Texture::ResponseTexture($this->skinData);
                $this->skinUrl = $this->getSkinUrl();
                $this->skinSlim = $this->checkIsSlim();
            }
        }
        if ($capeAlreadyDetected === false && Config::GIVE_DEFAULT_CAPE && in_array($responseType, [ResponseTypeEnum::JSON, ResponseTypeEnum::CAPE])) {
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
    private function getSkinUrl(): string
    {
        return (string)(new RequestParams)
            ->withEnum(ResponseTypeEnum::SKIN)
            ->withEnum(TextureStorageTypeEnum::DEFAULT)
            ->setVariable('login', NULL);
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
    private function getCapeUrl(): string
    {
        return (string)(new RequestParams)
        ->withEnum(ResponseTypeEnum::CAPE)
        ->withEnum(TextureStorageTypeEnum::DEFAULT)
        ->setVariable('login', NULL);
    }
}
