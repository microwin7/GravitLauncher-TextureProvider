<?php

namespace Microwin7\PHPUtils\Configs;

class MainConfig
{
    // Подключение к БД сайта
    public const DB_HOST = 'localhost';
    public const DB_NAME = 'test';
    public const DB_USER = 'test';
    public const DB_PASS = 'test';
    public const DB_PORT = '3306';
    /**
     * Тип расширения PHP для работы с базой данных
     * MySQLi | PDO
     */
    public const DB_DRIVER = 'PDO';
    /**
     * DSN префикс для PDO
     * [mysql] - MySQL сервер
     * [pgsql] - PostgreSQL сервер. Работает только с DB_DRIVER = 'PDO'
     */
    public const DB_SUD_DB = 'mysql';
    // Префикс БД для SERVERS. Не относиться к текущему проекту, нужен только для инициализации драйвера БД
    public const DB_PREFIX = 'server_';
    // Запись в файлы лога SQL запросов и их ошибок
    public const DEBUG = false;
    public const BEARER_TOKEN = '';

    public const SERVERS = [];
    public const MODULES = [
        'LuckPerms' => [
            'DB_NAME' => 'LuckPerms',
            'prefix' => 'luckperms_',
        ],
        /**
         * EXECUTE, SELECT
         */
        'TextureProvider' => [
            'DB_NAME' => 'site',
            /**
             * UserStorageTypeEnum::DB_USER_ID
             */
            'table_user' => 'users',
            /**
             * UserStorageTypeEnum::DB_SHA1
             * UserStorageTypeEnum::DB_SHA256
             */
            'table_user_assets' => 'user_assets',
            /**
             * Колонка сопоставления
             */
            'uuid_column' => 'uuid',
            /**
             * UserStorageTypeEnum::DB_USER_ID
             */
            'DB_USER_ID' => 'user_id',
            /**
             * For UserStorageTypeEnum::DB_SHA1
             * or UserStorageTypeEnum::DB_SHA256
             */
            'hash_column' => 'hash',
            /**
             * ResponseTypeEnum::SKIN->name
             * ResponseTypeEnum::CAPE->name
             */
            'texture_type_column' => 'name',
        ],
    ];
}
