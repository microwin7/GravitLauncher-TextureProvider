<?php

namespace Microwin7\TextureProvider\Configs;

use Microwin7\PHPUtils\Configs\PathConfig;
use Microwin7\TextureProvider\Data\UserStorageTypeEnum;

class Config
{
    /**
     * Путь к скрипту
     * Автоматически добавится домен из константы PathConfig::APP_URL
     * Если включён индекс файл index.php, ссылку можно указать как:
     * 'texture-provider/', тогда обязательно оставить / в конце
     */
    const SCRIPT_URL = 'texture-provider/index.php';
    
    /**
     * Тип имени файлов для StorageType
     * USERNAME - [username.png]
     * UUID - [uuid.png]
     * DB_USER_ID - [user_id.png] работает только с связью с БД
     * DB_SHA1 - [sha1.png] работает только с связью с БД
     * DB_SHA256 - [sha256.png] работает только с связью с БД
     */
    const USER_STORAGE_TYPE = UserStorageTypeEnum::USERNAME;

    /**
     * Выдавать ли рандомный скин из коллекции?
     * Действует только на скины.
     * Приоритенее чем GIVE_DEFAULT_SKIN для скина
     */
    const GIVE_FROM_COLLECTION = false;
    /**
     * Путь для хранения коллекции рандомных скинов.
     * При включении этого типа, создать папку и закинуть скины.
     * Из папки .bin выполнить скрипт кеширования коллекции.
     * Команда для Linux: **./.bin/index**
     * Для Windows: запустить index.bat из папки ".bin/"
     * При каждом изменении содержимого папки рандомной коллекции, кешировать повторно
     */
    const SKIN_RANDOM_COLLECTION_PATH = PathConfig::ROOT_FOLDER . 'storage/skin_random_collection/';
    /**
     * Выдавать ли default скины, если они не обнаружены в других источниках?
     */
    const GIVE_DEFAULT_SKIN = true; // Выдавать ли этим скриптом default скины.
    /**
     * Выдавать ли default плащи, если они не обнаружены в других источниках?
     */
    const GIVE_DEFAULT_CAPE = false;

    const SKIN_RESIZE = true;  // Скины преобразовываются на лету, копируя руку и ногу, для новых форматов. Чинит работу HD скинов с оптифайном на 1.16.5 версии
    // Для 1.7.10 требуется SkinPort (https://github.com/RetroForge/SkinPort/releases)
}
