<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Request\Loader;

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
        return '?' . http_build_query(
            [
                ResponseTypeEnum::getNameRequestVariable() => $this->responseType->name,
                TextureStorageTypeEnum::getNameRequestVariable() => $this->textureStorageType->name,
                'login' => $this->login,
            ]
        );
    }
}
