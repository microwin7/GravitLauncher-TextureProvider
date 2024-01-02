<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture;

use Microwin7\PHPUtils\Contracts\Texture\Models\Skin as ModelSkin;

final class Skin extends ModelSkin
{
    /** @return array{url: string, digest: string, metadata?: array{model: 'slim'}} */
    public function jsonSerialize(): array
    {
        $json = [
            'url' => Texture::urlComplete($this->textureStorageType, $this->url),
            'digest' => $this->digest,
        ];
        if ($this->isSlim) $json['metadata'] = ['model' => 'slim'];
        return $json;
    }
}
