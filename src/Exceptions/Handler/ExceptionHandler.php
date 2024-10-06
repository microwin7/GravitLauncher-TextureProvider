<?php

namespace Microwin7\PHPUtils\Exceptions\Handler;

use Microwin7\PHPUtils\Main;
use Composer\InstalledVersions;
use Microwin7\PHPUtils\Security\BearerToken;
use Microwin7\PHPUtils\Response\JsonResponse;
use Microwin7\PHPUtils\Exceptions\FileUploadException;
use Microwin7\PHPUtils\Exceptions\TextureSizeException;
use Microwin7\PHPUtils\Exceptions\TextureLoaderException;
use Microwin7\PHPUtils\Exceptions\ValidateBearerTokenException;
use Microwin7\PHPUtils\Exceptions\RegexArgumentsFailedException;
use Microwin7\PHPUtils\Exceptions\RequiredArgumentMissingException;

class ExceptionHandler
{
    public function __construct()
    {
        if (Main::SENTRY_ENABLE()) {
            \Sentry\init(['dsn' => Main::SENTRY_DSN()]);
            \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                $scope->setContext('application', [
                    'url' => Main::getApplicationURL()
                ]);
            });
            \Sentry\SentrySdk::getCurrentHub()->configureScope(function (\Sentry\State\Scope $scope) {
                $packages = function (): array {
                    $packages = [];
                
                    foreach (InstalledVersions::getInstalledPackages() as $package) {
                        $packages[$package] = InstalledVersions::getPrettyVersion($package);
                    }
                
                    return $packages;
                };
                $scope->setContext('composer_packages', $packages());
            });
        }
        /**
         * Sets a user-defined exception handler function
         */
        set_exception_handler([$this, 'exception_handler']);
    }
    public function exception_handler(\Throwable $e): void
    {
        if ($e instanceof \InvalidArgumentException) {
            $this->error('InvalidArgumentException');
            // provided key/key-array is empty or malformed.
        }
        if ($e instanceof \DomainException) {
            if (Main::SENTRY_ENABLE()) \Sentry\captureException($e);
            $this->error('DomainException');
            // provided algorithm is unsupported OR
            // provided key is invalid OR
            // unknown error thrown in openSSL or libsodium OR
            // libsodium is required but not available.
        }
        if ($e instanceof \Firebase\JWT\SignatureInvalidException) {
            $message = 'Неправильная сигнатура публичного ключа. Проверьте настройку environment: LAUNCH_SERVER_ECDSA256_PUBLIC_KEY_BASE64';
            if (Main::SENTRY_ENABLE()) {
                $authorization = BearerToken::getBearerToken() ?? '';
                $JWT_DATA = [];
                // Используем регулярное выражение для извлечения второй части JWT токена
                if (preg_match('/(.*)\.(.*)\.(.*)$/', $authorization, $matches)) {
                    /** @var array{string: string} */
                    $JWT_DATA = json_decode(base64_decode($matches[2]) ?: '{}', true) ?: [];
                }
                \Sentry\addBreadcrumb(new \Sentry\Breadcrumb(
                    \Sentry\Breadcrumb::LEVEL_INFO,
                    \Sentry\Breadcrumb::TYPE_HTTP,
                    'JWT_DATA',
                    '',
                    $JWT_DATA
                ));
                \Sentry\captureMessage($message, \Sentry\Severity::fatal());
            }
            $this->error($message);
            // provided JWT signature verification failed.
        }
        if ($e instanceof \Firebase\JWT\BeforeValidException) {
            if (Main::SENTRY_ENABLE()) \Sentry\captureException($e);
            $this->error('BeforeValidException');
            // provided JWT is trying to be used before "nbf" claim OR
            // provided JWT is trying to be used before "iat" claim.
        }
        if ($e instanceof \Firebase\JWT\ExpiredException) {
            $this->error('Токен авторизации истёк. Перезапустите лаунчер');
            // provided JWT is trying to be used after "exp" claim.
        }
        if ($e instanceof \UnexpectedValueException) {
            $message = 'Ваш текущий тип AuthCoreProvider не поддерживает JWT токены, необходимый для авторизации при использовании API';
            if (Main::SENTRY_ENABLE()) \Sentry\captureMessage($message, \Sentry\Severity::fatal());
            $this->error($message);
            // provided JWT is malformed OR
            // provided JWT is missing an algorithm / using an unsupported algorithm OR
            // provided JWT algorithm does not match provided key OR
            // provided key ID in key/key-array is empty or invalid.
        }
        if (
            $e instanceof ValidateBearerTokenException ||
            $e instanceof RequiredArgumentMissingException ||
            $e instanceof FileUploadException ||
            $e instanceof TextureSizeException ||
            $e instanceof TextureLoaderException ||
            $e instanceof RegexArgumentsFailedException ||
            $e instanceof \RuntimeException
        ) {
            $this->error($e);
        }
        if ($e instanceof \ErrorException) {
            if (Main::SENTRY_ENABLE()) \Sentry\captureException($e);
            $this->error('ErrorException');
        }
        if ($e instanceof \Throwable) {
            if (Main::SENTRY_ENABLE()) \Sentry\captureException($e);
            $this->error($e);
        }
    }
    private function error(\Throwable|string $error): never
    {
        if ($error instanceof \Throwable) {
            JsonResponse::failed(error: $error->getMessage());
        }
        JsonResponse::failed(error: $error);
    }
}
