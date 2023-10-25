<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture;

use JsonSerializable;

enum TextureStorageTypeEnum: int implements JsonSerializable
{
    case STORAGE        = 0;
    case MOJANG         = 1;
    case COLLECTION     = 2;
    case DEFAULT        = 3;

    private const CASES = [
        0 => 'STORAGE',
        1 => 'MOJANG',
        2 => 'COLLECTION',
        3 => 'DEFAULT',
    ];

    public static function fromString(string $name): static
    {
        return static::tryFromString($name)
            ?? throw new \InvalidArgumentException(sprintf('Unknown texture storage type: %s', $name));
    }

    public static function tryFromString(string $name): ?static
    {
        return (false !== $found = array_search(strtoupper($name), static::CASES, true))
            ? static::from($found)
            : null;
    }

    public function next(): TextureStorageTypeEnum
    {
        if ($this->value + 1 < count($this->cases()))
            return $this->from($this->value + 1);
        else return $this;
    }

    public function toString(): string
    {
        return static::CASES[$this->value];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toString();
    }
}
