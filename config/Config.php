<?php

namespace Microwin7\TextureProvider;

use Microwin7\PHPUtils\Configs\PathConfig;
use Microwin7\PHPUtils\Contracts\User\UserStorageTypeEnum;

class Config
{
    /**
     * Путь к скрипту
     * Автоматически добавится домен из константы PathConfig::APP_URL
     * Если включён индекс файл index.php, ссылку можно указать как:
     * 'texture-provider/', тогда обязательно оставить / в конце
     */
    public const string SCRIPT_URL = 'texture-provider/public/index.php';

    /**
     * Тип имени файлов для StorageType
     * USERNAME - [username.png]
     * UUID - [uuid.png]
     * DB_USER_ID - [user_id.png] работает только с связью с БД
     * DB_SHA1 - [sha1.png] работает только с связью с БД
     * DB_SHA256 - [sha256.png] работает только с связью с БД
     */
    public const UserStorageTypeEnum USER_STORAGE_TYPE = UserStorageTypeEnum::DB_SHA256;

    /**
     * Выдавать ли рандомный скин из коллекции?
     * Действует только на скины.
     * Приоритенее чем GIVE_DEFAULT_SKIN для скина
     */
    public const bool GIVE_FROM_COLLECTION = false;
    /**
     * При несоответсвтии хеш суммы файла в кеше,
     * либо при ненахождение файла из кеша в файловой системе,
     * сколько раз пытаться перегенерировать кеш и повторить вызов метода?
     */
    public const int MAX_RE_GENERATE_CACHE_COUNT = 1;
    /**
     * Путь для хранения коллекции рандомных скинов.
     * При включении этого типа, создать папку и закинуть скины.
     * Из папки .bin выполнить скрипт кеширования коллекции.
     * Команда для Linux: **./.bin/index**
     * Для Windows: запустить index.bat из папки ".bin/"
     * При каждом изменении содержимого папки рандомной коллекции, кешировать повторно
     */
    public const string SKIN_RANDOM_COLLECTION_PATH = PathConfig::ROOT_FOLDER . 'storage/skin_random_collection/';
    /**
     * Выдавать ли default скины, если они не обнаружены в других источниках?
     */
    public const bool GIVE_DEFAULT_SKIN = true; // Выдавать ли этим скриптом default скины.
    /**
     * Выдавать ли default плащи, если они не обнаружены в других источниках?
     */
    public const bool GIVE_DEFAULT_CAPE = false;

    public const bool SKIN_RESIZE = true;  // Скины преобразовываются на лету, копируя руку и ногу, для новых форматов. Чинит работу HD скинов с оптифайном на 1.16.5 версии
    // Для 1.7.10 требуется SkinPort (https://github.com/RetroForge/SkinPort/releases)

    /**
     * Ограничение для загрузки HD скинов и плащей по правам LuckPerms
     * При использовании, обязательно настроить подключение к БД в MainConfig
     */
    public const bool USE_LUCKPERMS_PERMISSION_HD_SKIN = false;
    /**
     * Минимальный веса группы пользователя из LuckPerms для предоставления права устанавливать HD текстуры
     * Число должно быть больше 0, 0 - группа по умолчанию, ограничивать не имеет смысла
     */
    public const int MIN_WEIGHT = 10;
}
