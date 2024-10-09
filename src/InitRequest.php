<?php

namespace Microwin7\TextureProvider;

use Microwin7\PHPUtils\Main;
use FastRoute\ConfigureRoutes;
use GuzzleHttp\Psr7\ServerRequest;
use Microwin7\PHPUtils\Utils\Path;
use Microwin7\PHPUtils\Rules\Regex;
use Microwin7\PHPUtils\Utils\Texture;
use Microwin7\TextureProvider\Config;
use FastRoute\Dispatcher\Result\Matched;
use Microwin7\TextureProvider\Data\User;
use Microwin7\TextureProvider\Utils\Cache;
use FastRoute\Dispatcher\Result\NotMatched;
use Psr\Http\Message\UploadedFileInterface;
use Microwin7\PHPUtils\Security\BearerToken;
use Microwin7\TextureProvider\Utils\GDUtils;
use Microwin7\PHPUtils\Response\JsonResponse;
use Microwin7\PHPUtils\Attributes\AsArguments;
use FastRoute\Dispatcher\Result\MethodNotAllowed;
use Microwin7\PHPUtils\Attributes\RegexArguments;
use Microwin7\PHPUtils\Request\RequiredArguments;
use Microwin7\TextureProvider\Data\UserDataFromJWT;
use Microwin7\PHPUtils\Contracts\Component\Enum\HTTP;
use Microwin7\PHPUtils\Exceptions\FileUploadException;
use Microwin7\PHPUtils\Contracts\Texture\Enum\MethodTypeEnum;
use Microwin7\TextureProvider\Request\Provider\RequestParams;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\TextureProvider\Texture\Texture as TextureProvider;
use Microwin7\PHPUtils\Exceptions\RequiredArgumentMissingException;
use Microwin7\PHPUtils\Contracts\Texture\Enum\TextureStorageTypeEnum;
use Microwin7\TextureProvider\Request\Loader\RequestParams as RequestParamsLoader;
use Microwin7\TextureProvider\Request\Provider\RequestParams as RequestParamsProvider;

class InitRequest
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    public RequestParamsProvider $requestParams;
    public Matched|NotMatched|MethodNotAllowed $routeInfo;

    function __construct()
    {
        $this->initRoute();
        $this->postInit();
    }

    private function initRoute(): void
    {
        /** @psalm-suppress DeprecatedFunction */
        $dispatcher = \FastRoute\simpleDispatcher(function (ConfigureRoutes $r) {
            $r->addRoute(
                HTTP::GET->name,
                '/{' . ResponseTypeEnum::getNameRequestVariable() . ':(?:SKIN|1|CAPE|2)}/{' .
                    TextureStorageTypeEnum::getNameRequestVariable() . ':(?:STORAGE|0|COLLECTION|2)}/{login:(?:[0-9]+|\w{2,16}|[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}|[0-9a-f]{32}|[0-9a-f]{40}|[0-9a-f]{64})}',
                'provider'
            );
            $r->addRoute(
                HTTP::GET->name,
                '/{' . ResponseTypeEnum::getNameRequestVariable() . ':(?:SKIN|1|CAPE|2)}/{' .
                    TextureStorageTypeEnum::getNameRequestVariable() . ':(?:DEFAULT|3)}',
                'provider'
            );
            $r->addRoute(
                HTTP::GET->name,
                '/{' . MethodTypeEnum::getNameRequestVariable() . ':(?:MOJANG|1|HYBRID|2)}/{username:\w{2,16}}/{uuid:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}}',
                'provider'
            );
            $r->addRoute(
                HTTP::GET->name,
                '/{' . ResponseTypeEnum::getNameRequestVariable() . ':(?:AVATAR|FRONT)}/{size:(?:[0-9]{2,3})}/{login:(?:[0-9]+|\w{2,16}|[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}|[0-9a-f]{32}|[0-9a-f]{40}|[0-9a-f]{64})}[{timestamp:(?:\?t=[0-9]{1,11}|\&t=[0-9]{1,11})}]',
                'returner'
            );
            $r->addRoute(
                HTTP::GET->name,
                '/{' . ResponseTypeEnum::getNameRequestVariable() . ':(?:AVATAR|FRONT)}/{login:(?:[0-9]+|\w{2,16}|[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}|[0-9a-f]{32}|[0-9a-f]{40}|[0-9a-f]{64})}[{timestamp:(?:\?t=[0-9]{1,11}|\&t=[0-9]{1,11})}]',
                'returner'
            );
            $r->addRoute(
                HTTP::GET->name,
                '/{username:(?:\w{2,16})}/{uuid:(?:[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12})}',
                'provider'
            );
            $r->addRoute(
                HTTP::POST->name,
                '/upload/{' . ResponseTypeEnum::getNameRequestVariable() . ':(?:SKIN|1|CAPE|2)}',
                'upload'
            );
            $r->addRoute(
                HTTP::POST->name,
                '/api/upload/{' . ResponseTypeEnum::getNameRequestVariable() . ':(?:SKIN|1|CAPE|2)}',
                'upload_api'
            );
        });

        /**
         * @var string $_SERVER['REQUEST_METHOD']
         * @var string $_SERVER['REQUEST_URI']
         */
        $request_uri = str_replace(
            Path::SCRIPT_PATH(),
            '',
            $_SERVER['REQUEST_URI']
        );
        $this->routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $request_uri);
    }
    private function postInit(): void
    {
        if ($this->routeInfo instanceof Matched) {
            switch ($this->routeInfo->handler) {
                case 'provider':
                    if (($this->requestParams = (new RequestParamsProvider)->fromRoute($this->routeInfo->variables))->responseType === ResponseTypeEnum::JSON) new BearerToken;
                    JsonResponse::response(new TextureProvider(new User($this->requestParams)));
                    break;
                case 'upload':
                    // Token signature verification and get username, uuid out JWT
                    /** @var object{sub: string, uuid: string} */
                    $JWT_DATA = UserDataFromJWT::getUserAndValidate();
                    if (isset($_FILES['file'])) {
                        /** @var UploadedFileInterface */
                        $file = ServerRequest::normalizeFiles($_FILES)['file'];
                        JsonResponse::response(
                            TextureProvider::loadTexture(
                                /** AutoInit ResponseTypeEnum from request, validate after only SKIN or CAPE */
                                (RequestParamsLoader::fromRoute($this->routeInfo->variables))
                                    /** Variable username for UserStorageTypeEnum::USERNAME in Config::USER_STORAGE_TYPE */
                                    ->setVariable('username', $JWT_DATA->sub)
                                    /** Variable uuid for other enum types in Config::USER_STORAGE_TYPE */
                                    ->setVariable('uuid', $JWT_DATA->uuid),
                                $file,
                                Config::HD_TEXTURES_ALLOW()
                            )
                        );
                    } else throw new FileUploadException(UPLOAD_ERR_NO_FILE);
                case 'upload_api':
                    $this->upload_api();
                case 'returner':
                    try {
                        $this->requestParams = RequestParams::fromRouteReturner($this->routeInfo->variables);
                    } catch (RequiredArgumentMissingException | \ValueError) {
                        TextureProvider::ResponseTexture(null);
                    }
                    $size = $this->requestParams->size ?? null;
                    switch ($this->requestParams->responseType) {
                        case ResponseTypeEnum::AVATAR:
                            $size ??= Config::AVATAR_CANVAS() ?? Config::BLOCK_CANVAS();
                            break;
                        case ResponseTypeEnum::CAPE_RESIZE:
                            $size ??= Config::CAPE_CANVAS();
                            break;
                        default:
                            $size ??= Config::BLOCK_CANVAS();
                            break;
                    }
                    /** @var int $size */
                    if ($size > Config::BOUND_WIDTH_CANVAS()) $size = Config::BLOCK_CANVAS();
                    switch ($this->requestParams->responseType) {
                        case ResponseTypeEnum::AVATAR:
                        case ResponseTypeEnum::FRONT:
                            /** @var string $this->requestParams->login */
                            $filename = Texture::PATH($this->requestParams->responseType, $this->requestParams->login, Texture::EXTENSTION(), $size);
                            if (!file_exists($filename)) {
                                Cache::saveCacheFile(
                                    $this->requestParams->login,
                                    GDUtils::{strtolower($this->requestParams->responseType->name)}(
                                        GDUtils::pre_calculation(TextureProvider::getSkinDataForAvatar($this->requestParams->login)),
                                        $size
                                    ),
                                    $this->requestParams->responseType,
                                    $size
                                );
                            }
                            TextureProvider::ResponseTexture(Cache::loadCacheFile($filename), Cache::getLastModified($filename));
                            break;
                        default:
                            TextureProvider::ResponseTexture(null);
                    }
                    break;
                default:
                    # code...
                    break;
            }
        }
        if ($this->routeInfo instanceof NotMatched || $this->routeInfo instanceof MethodNotAllowed) {
            TextureProvider::ResponseTexture(null);
        }
    }
    #[AsArguments(whereSearch: HTTP::POST, required: ['username', 'uuid'], optional: ['hd_allow'])]
    #[RegexArguments('username', Regex::USERNAME)]
    #[RegexArguments('uuid', Regex::UUIDv1_AND_v4)]
    #[RegexArguments('hd_allow', Regex::BOOLEAN_REGXP)]
    private function upload_api(): void
    {
        if (Main::BEARER_TOKEN() === null) throw new \RuntimeException('Обращение к API без включения проверки по Bearer токену недопустимо');
        new BearerToken;

        /**
         * @var string $args->username
         * @var string $args->uuid
         * @var bool|null $args->hd_allow
         */
        $args = new RequiredArguments(new \ReflectionMethod(static::class, __FUNCTION__));


        if (isset($_FILES['file'])) {
            /** @var UploadedFileInterface */
            $file = ServerRequest::normalizeFiles($_FILES)['file'];
            JsonResponse::response(
                TextureProvider::loadTexture(
                    /** AutoInit ResponseTypeEnum from request, validate after only SKIN or CAPE */
                    (RequestParamsLoader::fromRoute($this->routeInfo->variables))
                        /** Variable username for UserStorageTypeEnum::USERNAME in Config::USER_STORAGE_TYPE */
                        ->setVariable('username', $args->username)
                        /** Variable uuid for other enum types in Config::USER_STORAGE_TYPE */
                        ->setVariable('uuid', $args->uuid),
                    $file,
                    (null !== $args->hd_allow ? $args->hd_allow : Config::HD_TEXTURES_ALLOW())
                )
            );
        } else throw new FileUploadException(UPLOAD_ERR_NO_FILE);
    }
}
