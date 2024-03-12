<?php

use Microwin7\PHPUtils\Rules\Regex;
use Microwin7\PHPUtils\Utils\GDUtils;
use Microwin7\PHPUtils\Utils\Texture;
use Microwin7\TextureProvider\Config;
use Microwin7\PHPUtils\Configs\PathConfig;
use Microwin7\PHPUtils\Configs\TextureConfig;
use Microwin7\TextureProvider\Texture\Storage\DefaultType;
use Microwin7\TextureProvider\Texture\Storage\StorageType;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\TextureProvider\Texture\Texture as ProviderTexture;

#
# Скрипт отдачи текстур и их модификаций
#
# Начат перенос и обновление из https://github.com/microwin7/TextureReturner
# Исправно работает только при наличии скина и включённой Config::GIVE_DEFAULT_SKIN
# Пока что только проверен метод avatar после переноса
#

// ini_set('error_reporting', E_ALL); // FULL DEBUG
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require_once(__DIR__ . '/../vendor/autoload.php');

start();
class Constants
{
    public const CACHE_FOLDER = PathConfig::ROOT_FOLDER . "storage/returner_cache/"; // mkdir storage && chown -R www-data:www-data storage/
    public const int BLOCK_CANVAS = 128; // Ширина блока для таких методов как front, back, применяется к avatar, если размер CANVAS указан как null
    public const int CAPE_CANVAS = 16; // Умножится на минимальную ширину в 22 пикселя
    public const int BOUND_WIDTH_CANVAS = 512; // Предел ширины, для ограничения нагрузки вызываемого параметра size. Действует только на front, back, avatar, cape_resize == элементы работающие с canvas полем и преобразованием размера
    /**
     * Кеширование front, back, avatar, cape_resize в секундах
     * Выставить 60, если используется хранение по username, uuid, user_id, а не хеш сумме
     */
    public const ?int IMAGE_CACHE_TIME = null;

    public static function getSkin(string|null $login)
    {
        $storageType = new StorageType($login, null, null, ResponseTypeEnum::AVATAR);
        if (!is_null($storageType->skinData)) return $storageType->skinData;
        $storageType = new DefaultType(ResponseTypeEnum::AVATAR, false, true);
        if (!is_null($storageType->skinData)) return $storageType->skinData;
        else ProviderTexture::ResponseTexture(null);
        // $filename = Utils::ci_find_file(Texture::getSkinPath($login));
        // return $filename ? file_get_contents($filename) : (Config::GIVE_DEFAULT_SKIN ? base64_decode(TextureConfig::SKIN_DEFAULT) : ProviderTexture::ResponseTexture(null));
    }
    public static function getCape(string|null $login)
    {
        ProviderTexture::ResponseTexture(null);
        // $filename = Utils::ci_find_file(Texture::getCapePath($login));
        // return $filename ? file_get_contents($filename) : (Config::GIVE_DEFAULT_CAPE ? base64_decode(TextureConfig::CAPE_DEFAULT) : ProviderTexture::ResponseTexture(null));
    }
}
class Occurrences
{
    public static $requiredUrl = null;
    public static $queries = null;
    public static $login = null;
    public static $method = null;
    public static $size = null;

    function __construct()
    {
        self::requiredUrl();
        self::getQueries();
        self::getLogin();
        self::getMethod();
        self::getSize(self::$method);
    }
    public static function requiredUrl(): string
    {
        if (self::$requiredUrl == null) {
            $requiredUrl = $_SERVER['QUERY_STRING'] ?? null;
            !empty($requiredUrl) ?: ProviderTexture::ResponseTexture(null);
            return self::$requiredUrl = $requiredUrl;
        } else return self::$requiredUrl;
    }
    public static function getQueries()
    {
        if (self::$queries == null) {
            $queries = array();
            parse_str(self::$requiredUrl, $queries);
            $new_queries = [];
            foreach ($queries as $k => $v) {
                $new_queries[str_replace('amp;', '', $k)] = $v;
            }
            return self::$queries = $new_queries;
        } else return self::$queries;
    }
    public static function getLogin(): string
    {
        if (self::$login == null) {
            $login = self::$queries['login'] ?? ProviderTexture::ResponseTexture(null);
            !empty($login) ?: ProviderTexture::ResponseTexture(null);
            Regex::valid_with_pattern($login, Regex::combineOR(Regex::NUMERIC_REGXP, Regex::USERNAME, Regex::UUIDv1_AND_v4, Regex::MD5, Regex::SHA1, Regex::SHA256)) ?: ProviderTexture::ResponseTexture(null);
            return self::$login = $login;
        } else return self::$login;
    }
    public static function getMethod()
    {
        if (self::$method == null) {
            $method = self::$queries[ResponseTypeEnum::getNameRequestVariable()] ?? null;
            !empty($method) ?: null;
            return self::$method = $method;
        } else return self::$method;
    }
    public static function getSize($method)
    {
        if (self::$size == null) {
            $size = self::$queries['size'] ?? null;
            switch ($method) {
                case 'avatar':
                    !empty($size) ? $size : $size = (Config::AVATAR_CANVAS ?? Constants::BLOCK_CANVAS);
                    break;
                case 'cape_resize':
                    !empty($size) ? $size : $size = Constants::CAPE_CANVAS;
                    break;
                default:
                    !empty($size) ? $size : $size = Constants::BLOCK_CANVAS;
                    break;
            }
            if ($size > Constants::BOUND_WIDTH_CANVAS) $size = Constants::BLOCK_CANVAS;
            return self::$size = $size;
        } else return self::$size;
    }
}
class Check
{
    public static function skin($login)
    {
        [$image, $x, $y, $fraction] = GDUtils::pre_calculation(Constants::getSkin($login));
        return [$image, $fraction, GDUtils::checkSkinSlimFromImage($image)];
    }
    public static function cacheValid($filename, $size)
    {
        if (!file_exists($filename)) return false;
        if (Constants::IMAGE_CACHE_TIME === null) return true;
        $time = filemtime($filename);
        if ($size != getimagesize($filename)) return false;
        if ($time <= time() - 1 * Constants::IMAGE_CACHE_TIME) return false;
        return true;
    }
}
class Utils
{
    public static function ci_find_file($filename)
    {
        if (file_exists($filename))
            return $filename;
        $directoryName = dirname($filename);
        $fileArray = glob($directoryName . '/*', GLOB_NOSORT);
        $fileNameLowerCase = strtolower($filename);
        foreach ($fileArray as $file) {
            if (strtolower($file) == $fileNameLowerCase) {
                return $file;
            }
        }
        return false;
    }
    public static function saveCacheFile($login, $canvas, $method)
    {
        $directory = Constants::CACHE_FOLDER . strtolower($method) . '/';
        if (!file_exists($directory))
            mkdir($directory, 0777, true);
        $filename = $directory . strtolower($login) . Texture::EXT();
        imagepng($canvas, $filename, 9);
    }
    public static function loadCacheFile($filename)
    {
        return file_get_contents($filename);
    }
    public static function removeCacheFiles($method)
    {
        foreach (glob(Constants::CACHE_FOLDER . strtolower($method) . '/*', GLOB_NOSORT) as $file) {
            if (time() - lstat($file)['ctime'] > Constants::IMAGE_CACHE_TIME * 2) {
                unlink($file);
            }
        }
    }
}
class Modifier
{
    public static function front($data, $size)
    {
        // Создано пока что только для скинов по шаблону 64x32
        [$image, $fraction] = $data;
        $canvas = GDUtils::create_canvas_transparent($size, $size * 2);
        $f_part = $fraction / 2;
        $canvas_front = GDUtils::create_canvas_transparent($fraction * 2, $fraction * 4);
        $canvas_arm = GDUtils::create_canvas_transparent($f_part, $f_part * 3);
        $canvas_leg = $canvas_arm;
        // Head
        imagecopy($canvas_front, $image, $f_part, 0, $fraction, $fraction, $fraction, $fraction);
        //Helmet
        imagecopy($canvas_front, $image, $f_part, 0, $fraction * 5, $fraction, $fraction, $fraction);
        // Torso
        imagecopy($canvas_front, $image, $f_part, $f_part * 2, $f_part * 5, $f_part * 5, $f_part * 2, $f_part * 3);
        //Left Arm
        imagecopy($canvas_arm, $image, 0, 0, $f_part * 11, $f_part * 5, $f_part, $f_part * 3);
        imagecopy($canvas_front, $canvas_arm, 0, $f_part * 2, 0, 0, $f_part, $f_part * 3);
        //Right Arm
        imageflip($canvas_arm, IMG_FLIP_HORIZONTAL);
        imagecopy($canvas_front, $canvas_arm, $f_part * 3, $f_part * 2, 0, 0, $f_part, $f_part * 3);
        //Left Leg
        imagecopy($canvas_leg, $image, 0, 0, $f_part, $f_part * 5, $f_part, $f_part * 3);
        imagecopy($canvas_front, $canvas_leg, $f_part, $f_part * 5, 0, 0, $f_part, $f_part * 3);
        //Right Leg
        imageflip($canvas_leg, IMG_FLIP_HORIZONTAL);
        imagecopy($canvas_front, $canvas_leg, $f_part * 2, $f_part * 5, 0, 0, $f_part, $f_part * 3);
        //Resize
        imagecopyresized($canvas, $canvas_front, 0, 0, 0, 0,   $size, $size * 2, $fraction * 2, $fraction * 4);
        return $canvas;
    }
    public static function back($data, $size)
    {
        // Создано пока что только для скинов по шаблону 64x32
        [$image, $fraction] = $data;
        $canvas = GDUtils::create_canvas_transparent($size, $size * 2);
        $f_part = $fraction / 2;
        $canvas_back = GDUtils::create_canvas_transparent($fraction * 2, $fraction * 4);
        $canvas_arm = GDUtils::create_canvas_transparent($f_part, $f_part * 3);
        $canvas_leg = $canvas_arm;
        // Head
        imagecopy($canvas_back, $image, $f_part, 0, $fraction * 3, $fraction, $fraction, $fraction);
        //Helmet
        imagecopy($canvas_back, $image, $f_part, 0, $fraction * 7, $fraction, $fraction, $fraction);
        // Torso
        imagecopy($canvas_back, $image, $f_part, $f_part * 2, $f_part * 8, $f_part * 5, $f_part * 2, $f_part * 3);
        //Left Arm
        imagecopy($canvas_arm, $image, 0, 0, $f_part * 13, $f_part * 5, $f_part, $f_part * 3);
        imagecopy($canvas_back, $canvas_arm, $f_part * 3, $f_part * 2, 0, 0, $f_part, $f_part * 3);
        //Right Arm
        imageflip($canvas_arm, IMG_FLIP_HORIZONTAL);
        imagecopy($canvas_back, $canvas_arm, 0, $f_part * 2, 0, 0, $f_part, $f_part * 3);
        //Left Leg
        imagecopy($canvas_leg, $image, 0, 0, $f_part * 3, $f_part * 5, $f_part, $f_part * 3);
        imagecopy($canvas_back, $canvas_leg, $f_part * 2, $f_part * 5, 0, 0, $f_part, $f_part * 3);
        //Right Leg
        imageflip($canvas_leg, IMG_FLIP_HORIZONTAL);
        imagecopy($canvas_back, $canvas_leg, $f_part, $f_part * 5, 0, 0, $f_part, $f_part * 3);
        //Resize
        imagecopyresized($canvas, $canvas_back, 0, 0, 0, 0,   $size, $size * 2, $fraction * 2, $fraction * 4);
        return $canvas;
    }
    public static function avatar($data, $size)
    {
        [$image, $fraction] = $data;
        $canvas = GDUtils::create_canvas_transparent($size, $size);
        $size_under = (int)floor($size / 1.1);
        if ($size_under % 2 !== 0) $size_under--;
        $size_part = ($size - $size_under) / 2;
        imagecopyresized(
            $canvas,
            $image,
            $size_part,
            $size_part,
            $fraction,
            $fraction,
            $size_under,
            $size_under,
            $fraction,
            $fraction
        );
        imagecopyresized(
            $canvas,
            $image,
            0,
            0,
            $fraction * 5,
            $fraction,
            $size,
            $size,
            $fraction,
            $fraction
        );
        return $canvas;
    }
    public static function cape_resize($data, $size)
    {
        $image = imagecreatefromstring($data);
        $width = imagesx($image);
        $fraction = $width / 64;
        $canvas = GDUtils::create_canvas_transparent($size * 22, $size * 17);
        imagecopyresized($canvas, $image, 0, 0, 0, 0, $size * 22, $size * 17, $fraction * 22, $fraction * 17);
        return $canvas;
    }
}
function start()
{
    $occurrences = new Occurrences();
    $login = $occurrences::$login;
    $method = strtolower($occurrences::$method);
    switch ($method) {
        // case 'front':
        // case 'back':
        case 'avatar':
            switch ($method) {
                default:
                    $filename = Constants::CACHE_FOLDER . strtolower($method) . '/' . strtolower($login) . Texture::EXT();
                    Utils::removeCacheFiles($method);
                    if (!Check::cacheValid($filename, $occurrences::$size)) {
                        Utils::saveCacheFile($login, Modifier::$method(Check::skin($login), $occurrences::$size), $method);
                    }
                    ProviderTexture::ResponseTexture(Utils::loadCacheFile($filename));
                    break;
            }
            break;
        // case 'cape_resize':
        //     switch ($method) {
        //         default:
        //             $filename = Constants::CACHE_FOLDER . strtolower($method) . '/' . strtolower($login) . Texture::EXT();
        //             Utils::removeCacheFiles($method);
        //             if (!Check::cacheValid($filename, $occurrences::$size * 22)) {
        //                 Utils::saveCacheFile($login, Modifier::$method(Constants::getCape($login), $occurrences::$size), $method);
        //             }
        //             ProviderTexture::ResponseTexture(Utils::loadCacheFile($filename));
        //             break;
        //     }
        //     break;
        default:
            ProviderTexture::ResponseTexture(null);
    }
}
