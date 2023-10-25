<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Data;

use JsonSerializable;

enum MethodTypeEnum: int implements JsonSerializable
{
    case NORMAL         = 0;
    case MOJANG         = 1;
    case HYBRID         = 2;

    private const CASES = [
        0 => 'NORMAL',
        1 => 'MOJANG',
        2 => 'HYBRID',
    ];

    public static function fromString(string $name): static
    {
        return static::tryFromString($name)
            ?? static::NORMAL;
    }

    public static function tryFromString(string $name): ?static
    {
        return (false !== $found = array_search(strtoupper($name), static::CASES, true))
            ? static::from($found)
            : null;
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
