<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Request\Provider;

use Microwin7\PHPUtils\Rules\Regex;
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
            ->addVariable('login', Regex::LOGIN, true)
            ->addVariable('username', Regex::USERNAME, true)
            ->addVariable('uuid', Regex::UUIDv1_AND_v4, true)
            ->addEnum(MethodTypeEnum::class, true);
    }
    public function __toString(): string
    {
        /**
         * @var ResponseTypeEnum::SKIN|ResponseTypeEnum::CAPE $this->responseType
         * @var TextureStorageTypeEnum|null $this->textureStorageType
         * @var string|null $this->login
         */
        return '?' .
            'type=' . $this->responseType->name .
            (null === $this->textureStorageType ? '' : '&storage=' . $this->textureStorageType->name) .
            (null === $this->login ? '' : '&login=' . $this->login);
    }
}
