<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture;

use Microwin7\PHPUtils\Contracts\Texture\Models\Cape as ModelCape;

final class Cape extends ModelCape
{
    /** @return array{url: string, digest: string} */
    public function jsonSerialize(): array
    {
        return [
            'url' => Texture::urlComplete($this->textureStorageType, $this->url),
            'digest' => $this->digest,
        ];
    }
}
