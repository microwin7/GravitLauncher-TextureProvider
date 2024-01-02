<?php

namespace Microwin7\TextureProvider\Data;

use Microwin7\TextureProvider\Request\Provider\RequestParams;
use Microwin7\PHPUtils\Contracts\Texture\Enum\MethodTypeEnum;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\PHPUtils\Exceptions\RequiredArgumentMissingException;
use Microwin7\PHPUtils\Contracts\Texture\Enum\TextureStorageTypeEnum;

final class User
{
    /**
     * Expected response
     * 
     * JSON
     * SKIN
     * CAPE
     *
     * @var ResponseTypeEnum
     */
    public readonly ResponseTypeEnum $responseType;
    /**
     * $login
     * Для восстановления
     * TextureStorageTypeEnum::STORAGE TextureStorageTypeEnum::COLLECTION
     */
    public ?string $login;
    public ?string $username;
    public ?string $uuid;
    /**
     * STORAGE
     * MOJANG
     * COLLECTION
     * DEFAULT
     *
     * @var TextureStorageTypeEnum
     */
    public TextureStorageTypeEnum $textureStorageType;
    /**
     * From query param
     * 
     * NORMAL
     * MOJANG
     * HYBRID
     *
     * @var MethodTypeEnum
     */
    public MethodTypeEnum $methodType;
    public function __construct(private RequestParams $requestParams)
    {
        /** @var ResponseTypeEnum */
        $this->responseType = $requestParams->responseType;
        /** @var string|null */
        $this->login = $requestParams->login;
        /** @var string|null */
        $this->username = $requestParams->username;
        /** @var string|null */
        $this->uuid = $requestParams->uuid;
        /** @var TextureStorageTypeEnum */
        $this->textureStorageType = $requestParams->textureStorageType;
        /** @var MethodTypeEnum */
        $this->methodType = $requestParams->methodType;
        $this->validParams();
    }

    private function validParams(): void
    {
        match ($this->responseType) {
            ResponseTypeEnum::JSON => $this->validParamsGenerateTexture(),
            ResponseTypeEnum::SKIN, ResponseTypeEnum::CAPE => $this->validParamsGetTexture()
        };
    }
    /** @throws RequiredArgumentMissingException */
    private function validParamsGenerateTexture(): void
    {
        null !== $this->username || null !== $this->uuid ?: throw new RequiredArgumentMissingException(['username', 'uuid']);
        $this->setStartTextureStorageTypeEnum();
    }
    /** @throws RequiredArgumentMissingException */
    private function validParamsGetTexture(): void
    {
        if ($this->login === null && $this->textureStorageType !== TextureStorageTypeEnum::DEFAULT)
            throw new RequiredArgumentMissingException('login');
        /** @var string */
        $this->username = $this->uuid = $this->login;
    }
    private function setStartTextureStorageTypeEnum(): void
    {
        $this->textureStorageType = match ($this->methodType) {
            MethodTypeEnum::MOJANG => TextureStorageTypeEnum::MOJANG,
            default => TextureStorageTypeEnum::STORAGE
        };
    }
}
