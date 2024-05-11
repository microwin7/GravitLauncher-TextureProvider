<?php

namespace Microwin7\TextureProvider\Utils;

use Microwin7\PHPUtils\Utils\Texture;
use Microwin7\TextureProvider\Config;
use Microwin7\PHPUtils\Helpers\FileSystem;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\PHPUtils\Exceptions\FileSystemException;

/** ПЕРЕПИСАТЬ */
class Cache
{
    public static function saveCacheFile(string $login, \GdImage $canvas, ResponseTypeEnum $responseType): void
    {
        $directory = Texture::TEXTURE_STORAGE_FULL_PATH($responseType);
        if (!file_exists($directory))
            FileSystem::mkdir($directory);
        $filename = Texture::PATH($responseType, $login);
        imagepng($canvas, $filename, 9) ?: throw FileSystemException::createForbidden();
    }
    public static function loadCacheFile(string $filename): string
    {
        return file_get_contents($filename);
    }
    public static function removeCacheFiles(ResponseTypeEnum $responseType): void
    {
        if (Config::IMAGE_CACHE_TIME() !== null) {
            foreach (glob(Texture::TEXTURE_STORAGE_FULL_PATH($responseType) . '*', GLOB_NOSORT) as $file) {
                /** @psalm-suppress PossiblyNullOperand */
                if (time() - lstat($file)['ctime'] > Config::IMAGE_CACHE_TIME() * 2) {
                    unlink($file);
                }
            }
        }
    }
    public static function cacheValid(string $filename, int $size): bool
    {
        if (!file_exists($filename)) return false;
        if (Config::IMAGE_CACHE_TIME() === null) return true;
        $time = filemtime($filename);
        if ($size != getimagesize($filename)) return false;
        /** @psalm-suppress PossiblyNullOperand */
        if ($time <= time() - 1 * Config::IMAGE_CACHE_TIME()) return false;
        return true;
    }
}
