<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture;

use JsonSerializable;
use Microwin7\TextureProvider\Utils\RequestParams;

class Skin implements JsonSerializable
{
    public function __construct(
        public readonly TextureStorageTypeEnum  $textureStorageType,
        public readonly string                  $data,
        public readonly string|RequestParams    $url,
        public readonly bool                    $isSlim,
    ) {
    }
    public function jsonSerialize(): array
    {
        $json = [
            'url' => Texture::urlComplete($this->textureStorageType, $this->url),
            'digest' => Texture::digest($this->data),
        ];
        if ($this->isSlim) $json['metadata'] = ['model' => 'slim'];
        return $json;
    }
}
