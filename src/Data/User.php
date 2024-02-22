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
     * Типы возможных запрашиваемых данных:
     * JSON
     * SKIN
     * CAPE
     * 
     * @todo Расширить, добавить AVATAR, подумать о хранилищах, только Storage или поддерживать другие
     */
    public readonly ResponseTypeEnum $responseType;
    /**
     * Для получения текстур из предподготовленных данных в JSON ответе скрипта
     * Является безопасным, так как нет уязвимости в том чтоб затребовать несуществующую текстуру,
     * будет выдан код ответа 404 при ненахождении текстуры по запросу
     * TextureStorageTypeEnum::STORAGE TextureStorageTypeEnum::COLLECTION
     */
    public ?string $login;
    public ?string $username;
    public ?string $uuid;
    /**
     * Порядок инициализации хранилищ:
     * STORAGE
     * MOJANG
     * COLLECTION
     * DEFAULT
     */
    public TextureStorageTypeEnum $textureStorageType;
    /**
     * Возможные методы получения:
     * NORMAL - Все кроме mojang и выключенных хранилищ в конфиге и типов текстур
     * MOJANG - Пропускается Storage хранилище
     * HYBRID - Все кроме выключенных хранилищ в конфиге и типов текстур
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
    /** Вызов валидатора, в зависимости от типа получаемых данных */
    private function validParams(): void
    {
        match ($this->responseType) {
            ResponseTypeEnum::JSON => $this->validParamsGenerateTexture(),
            default => $this->validParamsGetTexture()
        };
    }
    /** @throws RequiredArgumentMissingException */
    private function validParamsGenerateTexture(): void
    {
        null !== $this->username || null !== $this->uuid ?: throw new RequiredArgumentMissingException(['username', 'uuid']);
        $this->setStartTextureStorageTypeEnum();
    }
    /**
     * Должен применяться только для получения текстур
     * @todo В последующем username и uuid должны быть только null, для правильный логики, а необходимые данные браться напрямую из поля login
     * @throws RequiredArgumentMissingException */
    private function validParamsGetTexture(): void
    {
        if ($this->login === null && $this->textureStorageType !== TextureStorageTypeEnum::DEFAULT)
            throw new RequiredArgumentMissingException('login');
        /** @var string */
        $this->username = $this->uuid = $this->login;
    }
    /**
     * При вызове метода mojang, Storage сразу будет пропущен из инициализации
     */
    private function setStartTextureStorageTypeEnum(): void
    {
        $this->textureStorageType = match ($this->methodType) {
            MethodTypeEnum::MOJANG => TextureStorageTypeEnum::MOJANG,
            default => TextureStorageTypeEnum::STORAGE
        };
    }
}
