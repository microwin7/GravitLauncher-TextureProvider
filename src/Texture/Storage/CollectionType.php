<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture\Storage;

use Microwin7\TextureProvider\Utils\GDUtils;
use Microwin7\TextureProvider\Configs\Config;
use Microwin7\TextureProvider\Texture\Texture;
use Microwin7\TextureProvider\Data\ResponseTypeEnum;
use Microwin7\TextureProvider\Texture\TextureStorageTypeEnum;
use Microwin7\TextureProvider\Utils\IndexSkinRandomCollection;
use Microwin7\TextureProvider\Utils\RequestParams;
use TypeError;

class CollectionType
{
    public              ?string             $skinData = null;
    public readonly     RequestParams       $skinUrl;
    public readonly     bool                $skinSlim;

    private IndexSkinRandomCollection       $index;

    function __construct(
        public readonly string              $uuid,
                        ResponseTypeEnum    $responseType
    ) {
        $this->index = new IndexSkinRandomCollection;
        if (!is_null($this->skinData = $this->getSkinData())) {
            $this->skinResize();
            if ($responseType === ResponseTypeEnum::SKIN) Texture::ResponseTexture($this->skinData);
            $this->skinUrl = $this->getSkinUrl();
            $this->skinSlim = $this->checkIsSlim();
        }
    }
    private function getSkinData(): ?string
    {
        return $this->index->getDataFromUUID($this->uuid) ?: null;
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
                    'From UUID: ' . $this->uuid,
                ));
            }
        }
    }
    private function getSkinUrl(): RequestParams
    {
        return new RequestParams(ResponseTypeEnum::SKIN, TextureStorageTypeEnum::COLLECTION, $this->uuid);
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
                'From UUID: ' . $this->uuid,
            ));
        }
    }
}
