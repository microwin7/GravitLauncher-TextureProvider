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
    public static function saveCacheFile(string $login, \GdImage $canvas, ResponseTypeEnum $responseType, ?int $size): void
    {
        $directory = Texture::TEXTURE_STORAGE_FULL_PATH($responseType, $size);
        if (!file_exists($directory))
            FileSystem::mkdir($directory);
        $filename = Texture::PATH($responseType, $login, Texture::EXTENSTION(), $size);
        imagepng($canvas, $filename, 9) ?: throw FileSystemException::createForbidden($directory);
    }
    public static function loadCacheFile(string $filename): string
    {
        return file_get_contents($filename);
    }
    public static function getLastModified(string $filename): int
    {
        return lstat($filename)['ctime'];
    }
    public static function removeCacheFiles(ResponseTypeEnum $responseType, ?int $size): void
    {
        if (Config::IMAGE_CACHE_TIME() !== null) {
            $path = Texture::TEXTURE_STORAGE_FULL_PATH($responseType, $size);
            try {
                foreach ((new FileSystem)->findFiles(Texture::TEXTURE_STORAGE_FULL_PATH($responseType, $size), Texture::EXTENSTION(), 0) as $file) {
                    /** @psalm-suppress PossiblyNullOperand */
                    if (time() - lstat($file)['ctime'] > Config::IMAGE_CACHE_TIME() * 2) {
                        unlink($file);
                    }
                }
            } catch (FileSystemException $e) {
                if ($e->isErrorFolderNotExist()) FileSystem::mkdir($path);
            }
        }
    }
    public static function cacheValid(string $filename): bool
    {
        if (!file_exists($filename)) return false;
        if (Config::IMAGE_CACHE_TIME() === null) return true;
        $time = filemtime($filename);
        /** @psalm-suppress PossiblyNullOperand */
        if ($time <= time() - 1 * Config::IMAGE_CACHE_TIME()) return false;
        return true;
    }
}
