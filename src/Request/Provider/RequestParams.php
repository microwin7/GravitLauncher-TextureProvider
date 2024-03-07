<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Request\Provider;

use Microwin7\PHPUtils\Rules\Regex;
use Microwin7\TextureProvider\Config;
use Microwin7\PHPUtils\Request\RequestParamsAbstract;
use Microwin7\PHPUtils\Contracts\Texture\Enum\MethodTypeEnum;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\PHPUtils\Contracts\Texture\Enum\TextureStorageTypeEnum;

final class RequestParams extends RequestParamsAbstract
{
    /** @psalm-suppress MixedInferredReturnType */
    public static function fromRequest(self|\Closure|null $requestParams = null): static
    {
        $requestParams ??= new static();
        /** @psalm-suppress MixedReturnStatement */
        if ($requestParams instanceof \Closure) return $requestParams();
        return $requestParams->setOptions($_GET)
            ->addEnum(ResponseTypeEnum::class, true)
            ->addEnum(TextureStorageTypeEnum::class, true)
            ->addVariable('login', Regex::combineOR(Regex::NUMERIC_REGXP, Regex::USERNAME, Regex::UUIDv1_AND_v4, Regex::MD5, Regex::SHA1, Regex::SHA256), true)
            ->addVariable('username', Regex::USERNAME, true)
            ->addVariable('uuid', Regex::UUIDv1_AND_v4, true)
            ->addEnum(MethodTypeEnum::class, true);
    }
    public function __toString(): string
    {
        /**
         * @var ResponseTypeEnum::SKIN|ResponseTypeEnum::CAPE $this->responseType
         * @var TextureStorageTypeEnum::STORAGE|TextureStorageTypeEnum::COLLECTION|TextureStorageTypeEnum::DEFAULT $this->textureStorageType
         * @var string|null $this->login null while TextureStorageTypeEnum::DEFAULT
         */
        return match (Config::ROUTERING) {
            TRUE => implode('/', array_filter(
                [
                    Config::MINIMIZE_ENUM_REQUEST ? (string)$this->responseType->value : $this->responseType->name,
                    Config::MINIMIZE_ENUM_REQUEST ? (string)$this->textureStorageType->value : $this->textureStorageType->name,
                    $this->login
                ],
                function ($v) {
                    return $v !== null;
                }
            )),
            FALSE => 'index.php?' . http_build_query(
                [
                    ResponseTypeEnum::getNameRequestVariable() => Config::MINIMIZE_ENUM_REQUEST ? (string)$this->responseType->value : $this->responseType->name,
                    TextureStorageTypeEnum::getNameRequestVariable() => Config::MINIMIZE_ENUM_REQUEST ? (string)$this->textureStorageType->value : $this->textureStorageType->name,
                    'login' => $this->login,
                ]
            )
        };
    }
}
