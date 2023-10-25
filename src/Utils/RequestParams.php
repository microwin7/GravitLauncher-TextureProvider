<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Utils;

use ValueError;
use Microwin7\PHPUtils\Rules\Regex;
use Microwin7\TextureProvider\Data\MethodTypeEnum;
use Microwin7\TextureProvider\Data\ResponseTypeEnum;
use Microwin7\TextureProvider\Texture\TextureStorageTypeEnum;

final class RequestParams
{

    public static function fromRequest(?RequestParams $requestParams = null): static
    {
        $requestParams ??= new static();

        $options = $_GET;

        if (array_key_exists('type', $options)) {
            if (is_numeric($options['type']))
                $requestParams = $requestParams->withResponseType(ResponseTypeEnum::from((int)$options['type']));
            else
                $requestParams = $requestParams->withResponseType(ResponseTypeEnum::fromString($options['type']));
        }

        if (array_key_exists('storage', $options)) {
            if (is_numeric($options['storage']))
                $requestParams = $requestParams->withTextureStorageType(TextureStorageTypeEnum::from((int)$options['storage']));
            else
                $requestParams = $requestParams->withTextureStorageType(TextureStorageTypeEnum::fromString($options['storage']));
        }

        if (array_key_exists('login', $options)) {
            $requestParams = $requestParams->withLogin($options['login']);
        }

        if (array_key_exists('username', $options)) {
            $requestParams = $requestParams->withUsername($options['username']);
        }

        if (array_key_exists('uuid', $options)) {
            $requestParams = $requestParams->withUUID($options['uuid']);
        }

        if (array_key_exists('method', $options)) {
            if (is_numeric($options['method']))
                $requestParams = $requestParams->withMethodType(MethodTypeEnum::from((int)$options['method']));
            else
                $requestParams = $requestParams->withMethodType(MethodTypeEnum::fromString($options['method']));
        }

        return $requestParams;
    }

    public function __construct(
        public readonly ResponseTypeEnum       $responseType       = ResponseTypeEnum::JSON,
        public readonly TextureStorageTypeEnum $textureStorageType = TextureStorageTypeEnum::DEFAULT,
        public readonly ?string                $login              = null,
        public readonly ?string                $username           = null,
        public readonly ?string                $uuid               = null,
        public readonly MethodTypeEnum         $methodType         = MethodTypeEnum::NORMAL,
    ) {
        $this->validate();
    }
    private function validate(): void
    {
        null === $this->login
            ?: filter_var($this->login, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => Regex::LOGIN], 'flags' => FILTER_NULL_ON_FAILURE])
            ?: throw new ValueError(sprintf('Field "login" should be valid with pattern: [' . Regex::LOGIN . '], "%s" given', $this->login));
        null === $this->username
            ?: filter_var($this->username, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => Regex::USERNAME], 'flags' => FILTER_NULL_ON_FAILURE])
            ?: throw new ValueError(sprintf('Field "username" should be valid with pattern: [' . Regex::USERNAME . '], "%s" given', $this->username));
        null === $this->uuid
            ?: filter_var($this->uuid, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => Regex::UUIDv1_AND_v4], 'flags' => FILTER_NULL_ON_FAILURE])
            ?: throw new ValueError(sprintf('Field "uuid" should be valid with pattern: [' . Regex::UUIDv1_AND_v4 . '], "%s" given', $this->uuid));
    }
    private function with(string $property, mixed $value): static
    {
        return new static(...[$property => $value] + get_object_vars($this));
    }

    public function withResponseType(ResponseTypeEnum $responseType): static
    {
        return $this->with('responseType', $responseType);
    }

    public function withTextureStorageType(TextureStorageTypeEnum $textureStorageType): static
    {
        return $this->with('textureStorageType', null === $textureStorageType ?: $textureStorageType);
    }

    public function withLogin(string $login): static
    {
        return $this->with('login', null === $login ?: $login);
    }

    public function withUsername(string $username): static
    {
        return $this->with('username', null === $username ?: $username);
    }

    public function withUUID(string $uuid): static
    {
        return $this->with('uuid', null === $uuid ?: $uuid);
    }

    public function withMethodType(MethodTypeEnum $methodType): static
    {
        return $this->with('methodType', null === $methodType ?: $methodType);
    }

    public function __toString(): string
    {
        return '?' .
            'type=' . $this->responseType->name .
            (null === $this->textureStorageType ? '' : '&storage=' . $this->textureStorageType->name) .
            (null === $this->login ? '' : '&login=' . $this->login);
    }
}
