<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Data;

use JsonSerializable;

enum UserStorageTypeEnum: int implements JsonSerializable
{
    case USERNAME     = 0;
    case UUID         = 1;
    case DB_USER_ID   = 2;
    case DB_SHA1      = 3;
    case DB_SHA256    = 4;

    private const CASES = [
        0 => 'USERNAME',
        1 => 'UUID',
        2 => 'DB_USER_ID',
        3 => 'DB_SHA1',
        4 => 'DB_SHA256',
    ];

    public static function fromString(string $name): static
    {
        return static::tryFromString($name)
            ?? throw new \InvalidArgumentException(sprintf('Unknown user storage type: %s', $name));
    }

    public static function tryFromString(string $name): ?static
    {
        return (false !== $found = array_search($name, static::CASES, true))
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
