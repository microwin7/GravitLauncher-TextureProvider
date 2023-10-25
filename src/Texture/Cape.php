<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture;

use JsonSerializable;
use Microwin7\TextureProvider\Utils\RequestParams;

class Cape implements JsonSerializable
{
    public function __construct(
        public readonly TextureStorageTypeEnum  $textureStorageType,
        public readonly string                  $data,
        public readonly string|RequestParams    $url
    ) {
    }
    public function jsonSerialize(): array
    {
        return [
            'url' => Texture::urlComplete($this->textureStorageType, $this->url),
            'digest' => Texture::digest($this->data),
        ];
    }
}
