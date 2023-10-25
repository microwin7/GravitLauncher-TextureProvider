<?php

namespace Microwin7\PHPUtils\Configs;

class TextureConfig
{
    /**
     * Настройка полного пути хранения файлов
     * Пример при склеивании: /var/www/html/storage/skins/
     * Если DIRECTORY_SEPARATOR не будет обнаружен в конце, он будет добавлен принудительно
     */
    public const SKIN_PATH = PathConfig::ROOT_FOLDER . self::SKIN_URL_PATH;
    public const CAPE_PATH = PathConfig::ROOT_FOLDER . self::CAPE_URL_PATH;
    /**
     * Внешние пути хранения файлов, от корня сайта
     */
    public const SKIN_URL_PATH = 'storage/skins/';
    public const CAPE_URL_PATH = 'storage/capes/';
    /**
     * Для вызова, сохранения, проверок
     * Может быть пустой строкой, для таких файлов как хеш сумма
     * Точка будет добавлена автоматически
     */
    public const EXT = 'png';
    /**
     * Обвязка для получения по ключу, не менять!!!
     */
    public const TEXTURE_PATH = [
        'SKIN' => self::SKIN_PATH,
        'CAPE' => self::CAPE_PATH,
    ];
    /**
     * Максимальный размер загружаемого файла
     */
    public const MAX_SIZE_BYTES = 2 * 1024 * 1024; // byte => Kbyte, Kbyte => MB * 2

    public const SIZE = [
        'SKIN' => [['w' => 64, 'h' => 64], ['w' => 64, 'h' => 32]],
        'CAPE' => [['w' => 64, 'h' => 32]]
    ];
    public const SIZE_WITH_HD = [
        'SKIN' => [
            ['w' => 128, 'h' => 64], ['w' => 128, 'h' => 128],
            ['w' => 256, 'h' => 128], ['w' => 256, 'h' => 256],
            ['w' => 512, 'h' => 256], ['w' => 512, 'h' => 512],
            ['w' => 1024, 'h' => 512], ['w' => 1024, 'h' => 1024]
        ],
        'CAPE' => [
            ['w' => 128, 'h' => 64], ['w' => 256, 'h' => 128],
            ['w' => 512, 'h' => 256], ['w' => 1024, 'h' => 512]
        ]
    ];
    public const SKIN_DEFAULT = "iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgCAMAAACVQ462AAAAWlBMVEVHcEwsHg51Ri9qQC+HVTgjIyNO
    LyK7inGrfWaWb1udZkj///9SPYmAUjaWX0FWScwoKCgAzMwAXl4AqKgAaGgwKHImIVtGOqU6MYkAf38AmpoAr68/Pz9ra2t3xPtNAA
    AAAXRSTlMAQObYZgAAAZJJREFUeNrUzLUBwDAUA9EPMsmw/7jhNljl9Xdy0J3t5CndmcOBT4Mw8/8P4pfB6sNg9yA892wQvwzSIr8f
    5JRzSeS7AaiptpxazUq8GPQB5uSe2DH644GTsDFsNrqB9CcDgOCAmffegWWwAExnBrljqowsFBuGYShY5oakgOXs/39zF6voDG9r+w
    LvTCVUcL+uV4m6uXG/L3Ut691697tgnZgJavinQHOB7DD8awmaLWEmaNuu7YGf6XcIITRm19P1ahbARCRGEc8x/UZ4CroXAQTVIGL0
    YySrREBADFGicS8XtG8CTS+IGU2F6EgSE34VNKoNz8348mzoXGDxpxkQBpg2bWobjgZSm+uiKDYH2BAO8C4YBmbgAjpq5jUl4yGJC4
    6HQ7HJBfkeTAImIEmgmtpINi44JsHx+CKA/BTuArISXeBTR4AI5gK4C2JqRfPs0HNBkQnG8S4Yxw8IGoIZfXEBOW1D4YJDAdNSXgRe
    vP+ylK6fGBCwsWywmA19EtBkJr8K2t4N5pnAVwH0jptsBp+2gUFj4tL5ywAAAABJRU5ErkJggg==";
    public const CAPE_DEFAULT = "iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgAQMAAACYU+zHAAAAA1BMVEVHcEyC+tLSAAAAAXRSTlMAQObY
    ZgAAAAxJREFUeAFjGAV4AQABIAABL3HDQQAAAABJRU5ErkJggg==";
}
