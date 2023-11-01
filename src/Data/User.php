<?php

namespace Microwin7\TextureProvider\Data;

use Microwin7\TextureProvider\Data\MethodTypeEnum;
use Microwin7\TextureProvider\Utils\RequestParams;
use Microwin7\TextureProvider\Texture\TextureStorageTypeEnum;
use Microwin7\PHPUtils\Exceptions\RequiredArgumentMissingException;

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
     * @var string|null
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
    public ?TextureStorageTypeEnum $textureStorageType;
    /**
     * From query param
     * 
     * NORMAL
     * MOJANG
     * HYBRID
     *
     * @var MethodTypeEnum
     */
    public ?MethodTypeEnum $methodType;
    public function __construct(private RequestParams $requestParams)
    {
        $this->responseType = $requestParams->responseType;
        $this->login = $requestParams->login;
        $this->username = $requestParams->username;
        $this->uuid = $requestParams->uuid;
        $this->textureStorageType = $requestParams->textureStorageType;
        $this->methodType = $requestParams->methodType;
        $this->validParams();
    }

    private function validParams()
    {
        match ($this->responseType) {
            ResponseTypeEnum::JSON => $this->validParamsGenerateTexture(),
            ResponseTypeEnum::SKIN, ResponseTypeEnum::CAPE => $this->validParamsGetTexture()
        };
    }
    private function validParamsGenerateTexture()
    {
        null !== $this->username || null !== $this->uuid ?: throw new RequiredArgumentMissingException(['username', 'uuid']);
        $this->setStartTextureStorageTypeEnum();
    }
    private function validParamsGetTexture()
    {
        if ($this->login === null && $this->textureStorageType !== TextureStorageTypeEnum::DEFAULT
        ) throw new RequiredArgumentMissingException('login');
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
