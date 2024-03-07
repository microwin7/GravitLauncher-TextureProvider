<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Request\Loader;

use Microwin7\TextureProvider\Config;
use Microwin7\PHPUtils\Request\RequestParamsAbstract;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\PHPUtils\Contracts\Texture\Enum\TextureStorageTypeEnum;

final class RequestParams extends RequestParamsAbstract
{
    /** @psalm-suppress MixedInferredReturnType */
    public static function fromRequest(self|\Closure|null $requestParams = null): static
    {
        return new static();
    }
    public function __construct()
    {
        $this->setOptions($_REQUEST)
            ->addEnum(ResponseTypeEnum::class);
    }
    public function __toString(): string
    {
        /**
         * @var ResponseTypeEnum::SKIN|ResponseTypeEnum::CAPE $this->responseType
         * @var TextureStorageTypeEnum $this->textureStorageType
         * @var string $this->login
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
