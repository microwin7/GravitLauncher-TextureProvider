<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture;

use Microwin7\PHPUtils\Utils\Texture;
use Microwin7\TextureProvider\Texture\Texture as ProviderTexture;
use Microwin7\PHPUtils\Contracts\Texture\Models\Cape as ModelCape;

final class Cape extends ModelCape
{
    /** @return array{url: string, digest: string} */
    public function jsonSerialize(): array
    {
        return [
            'url' => ProviderTexture::urlComplete($this->textureStorageType, $this->url),
            'digest' => Texture::LEGACY_DIGEST() ? Texture::digest_legacy($this->data) : $this->digest,
        ];
    }
}
