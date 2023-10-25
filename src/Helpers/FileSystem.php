<?php

namespace Microwin7\TextureProvider\Helpers;

use UnexpectedValueException;
use Microwin7\PHPUtils\Configs\TextureConfig;
use Microwin7\PHPUtils\Exceptions\FileSystemException;

class FileSystem
{
    public function findFile(string $directory, string $fileName, string $extension): string|false
    {
        if ($this->is_dir($directory)) return $this->recursiveSearchNameFileCaseInsensitive($directory, $fileName, $extension, 0);
        else throw new FileSystemException("The folder does not exist or the script does not have read access");
    }
    public function findFiles(string $directory): array
    {
        if ($this->is_dir($directory)) return $this->recursiveSearchFiles($directory, 1);
        else throw new FileSystemException("The folder does not exist or the script does not have read access");
    }
    public function findFolder(string|array $folders): bool
    {
        if (is_string($folders)) {
            if ($this->is_dir($folders)) return true;
        }
        if (is_array($folders)) {
            foreach ($folders as $directory) {
                if ($this->is_dir($directory)) return true;
            }
        }
        return false;
    }
    public function is_file($path)
    {
        try {
            $path = preg_replace("/\/+$/", "", $path);
            if (is_file($path)) {
                return true;
            }
        } catch (\Exception $e) {
            exit("An unexpected error occurred while validating the file: " . $e->getMessage());
        }
        return false;
    }
    public function is_dir(&$directory)
    {
        try {
            $directory = preg_replace("/\/+$/", "", $directory);
            if (is_dir($directory)) {
                return true;
            }
        } catch (\Exception $e) {
            exit("An unexpected error occurred while validating the folder: " . $e->getMessage());
        }
        return false;
    }
    public function recursiveSearchNameFileCaseInsensitive(string $directory, string $fileName, string $extension, int $level = -1): string|false
    {
        try {
            $directory = preg_replace("/\/+$/", "", $directory);
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            if ($level > -1) {
                $iterator->setMaxDepth($level);
            }
            foreach ($iterator as $path => $obj) {
                if (!$obj->isDir()) {
                    $basename = pathinfo($path, PATHINFO_BASENAME);
                    if (strtolower($basename) === strtolower($fileName . '.' . $extension)) {
                        return mb_substr(
                            mb_stristr($basename, $fileName . '.' . $extension, false, mb_internal_encoding()),
                            0,
                            mb_strlen($fileName, mb_internal_encoding()),
                            mb_internal_encoding()
                        );
                    }
                }
            }
        } catch (UnexpectedValueException $e) {
            exit("An unexpected error occurred while searching for files: " . $e->getMessage());
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
        return false;
    }
    private function recursiveSearchFiles($directory, $level = -1): array
    {
        try {
            $filename = [];
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            if ($level > -1) {
                $iterator->setMaxDepth($level);
            }
            foreach ($iterator as $path => $obj) {
                if (!$obj->isDir()) {
                    $filetype = pathinfo($path, PATHINFO_EXTENSION);
                    if (strtolower($filetype) === TextureConfig::EXT) {
                        $filename[] = $path;
                    }
                }
            }
        } catch (UnexpectedValueException $e) {
            exit("An unexpected error occurred while searching for files: " . $e->getMessage());
        } catch (\Exception $e) {
            exit($e->getMessage());
        } finally {
            return $filename;
        }
    }
}
