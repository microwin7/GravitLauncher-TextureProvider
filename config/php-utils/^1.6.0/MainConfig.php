<?php

namespace Microwin7\PHPUtils\Configs;

use Microwin7\PHPUtils\DB\DriverTypeEnum;
use Microwin7\PHPUtils\DB\SubDBTypeEnum;

class MainConfig
{
    // Подключение к БД сайта
    public const string DB_HOST = 'localhost';
    public const string DB_NAME = 'test';
    public const string DB_USER = 'test';
    public const string DB_PASS = 'test';
    public const string DB_PORT = '3306';
    /**
     * DriverTypeEnum::PDO [SubDBTypeEnum::MySQL, SubDBTypeEnum::PostgreSQL]
     * DriverTypeEnum::MySQLi [SubDBTypeEnum::MySQL]
     */
    public const DriverTypeEnum DB_DRIVER = DriverTypeEnum::PDO; // MySQLi, PDO | Default: MySQLi
    /**
     * DSN префикс Sub DB для PDO
     * SubDBTypeEnum::MySQL
     * SubDBTypeEnum::PostgreSQL
     */
    public const SubDBTypeEnum DB_SUD_DB = SubDBTypeEnum::MySQL;
    public const array DB_PDO_OPTIONS = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_PERSISTENT => true
    ];
    // Префикс БД для SERVERS
    public const string DB_PREFIX = 'server_';
    // Запись в файлы лога SQL запросов и их ошибок
    public const bool DB_DEBUG = true;
    public const string|null BEARER_TOKEN = null;
    public const string PRIVATE_API_KEY = '';
    // https://base64.guru/converter/encode/file
    protected const string ECDSA256_PUBLIC_KEY_BASE64 = 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEbA97zpt+ASqCEozXGSaxzp5MSW/wadEktouZwfzDioZKjNN1dJP5Fzy+UjOA1H4E2NDpXvYqqwYoNNyNX8d/OQ==';
    protected const string ECDSA256_PUBLIC_KEY_PATH = '';
    public const bool SENTRY_ENABLE = false;
    public const string SENTRY_DSN = '';

    /** @var array<string, array<string, mixed>> */
    public const array SERVERS = [];
    /** @var array<string, array<string, string>> */
    public const array MODULES = [
        'LuckPerms' => [
            'DB_NAME' => 'LuckPerms',
            'prefix' => 'luckperms_',
        ],
        /**
         * EXECUTE, SELECT
         */
        'TextureProvider' => [
            /** Driver Connect Database */
            'DB_NAME' => 'site',
            'table_user' => [
                'TABLE_NAME' => 'users',
                /**
                 * Колонка связывания с table_user_assets
                 * Либо для получения User ID
                 * Example:
                 * 'user_id' for UserStorageTypeEnum::DB_USER_ID,
                 */
                'id_column' => 'user_id',
                'uuid_column' => 'uuid'
            ],
            /**
             * For UserStorageTypeEnum::DB_SHA1
             * or UserStorageTypeEnum::DB_SHA256
             */
            'table_user_assets' => [
                'TABLE_NAME' => 'user_assets',
                /**
                 * Колонка связывания с table_user
                 */
                'id_column' => 'user_id',
                /**
                 * key-of<ResponseTypeEnum::SKIN|ResponseTypeEnum::CAPE>
                 */
                'texture_type_column' => 'type',
                'hash_column' => 'hash',
                /** (NULL)|SLIM(1) */
                'texture_meta_column' => 'meta'
            ],
        ],
    ];
}
