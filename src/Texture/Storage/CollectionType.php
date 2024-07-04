<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture\Storage;

use TypeError;
use Microwin7\TextureProvider\Config;
use Microwin7\TextureProvider\Utils\GDUtils;
use Microwin7\TextureProvider\Texture\Texture;
use Microwin7\TextureProvider\Request\Provider\RequestParams;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\TextureProvider\Utils\IndexSkinRandomCollection;
use Microwin7\PHPUtils\Contracts\Texture\Enum\TextureStorageTypeEnum;

class CollectionType
{
    public              ?string             $skinData = null;
    /** @psalm-suppress PropertyNotSetInConstructor */
    public readonly     string              $skinUrl;
    /** @psalm-suppress PropertyNotSetInConstructor */
    public readonly     bool                $skinSlim;
    public readonly     null                $capeData;
    public readonly     string              $capeUrl;

    private IndexSkinRandomCollection       $index;

    function __construct(
        public readonly string              $uuid,
        ResponseTypeEnum    $responseType
    ) {
        $this->index = new IndexSkinRandomCollection;
        if (!is_null($this->skinData = $this->getSkinData())) {
            if ($responseType !== ResponseTypeEnum::JSON && $responseType !== ResponseTypeEnum::AVATAR) $this->skinResize();
            if ($responseType === ResponseTypeEnum::SKIN) Texture::ResponseTexture($this->skinData);
            $this->skinUrl = $this->getSkinUrl();
            $this->skinSlim = $this->checkIsSlim();
        }

        $this->capeData = null;
        $this->capeUrl = '';
    }
    private function getSkinData(): ?string
    {
        return $this->index->getDataFromUUID($this->uuid);
    }
    private function skinResize(): void
    {
        /** @var string $this->skinData */
        if (Config::SKIN_RESIZE()) {
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
    private function getSkinUrl(): string
    {
        return (string)(new RequestParams)
            ->withEnum(ResponseTypeEnum::SKIN)
            ->withEnum(TextureStorageTypeEnum::COLLECTION)
            ->setVariable('login', $this->uuid);
    }
    private function checkIsSlim(): bool
    {
        /** @var string $this->skinData */
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
