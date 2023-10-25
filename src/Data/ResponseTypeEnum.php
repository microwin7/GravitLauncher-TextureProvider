<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Data;

use JsonSerializable;

enum ResponseTypeEnum: int implements JsonSerializable
{
    case JSON         = 0;
    case SKIN         = 1;
    case CAPE         = 2;

    private const CASES = [
        0 => 'JSON',
        1 => 'SKIN',
        2 => 'CAPE',
    ];

    public static function fromString(string $name): static
    {
        return static::tryFromString($name)
            ?? throw new \InvalidArgumentException(sprintf('Unknown response type: %s', $name));
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
