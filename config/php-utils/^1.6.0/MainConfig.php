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
    public const bool DB_DEBUG = false;
    public const string|null BEARER_TOKEN = null;
    public const string PRIVATE_API_KEY = '';
    // https://base64.guru/converter/encode/file
    protected const string ECDSA256_PUBLIC_KEY_BASE64 = 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEkS1KRgLNghgs5uaMxoAcTtZbY/ROWO88hHOVLpzHY66QZGF8prDlDm+tK1Ju67s0RSovOLvmA+FdvTDv8yuCAw==';
    protected const string ECDSA256_PUBLIC_KEY_PATH = '';
    public const bool SENTRY_ENABLE = true;
    public const string SENTRY_DSN = 'https://dce66e1d574a0066589421bda5c36b2f@o484368.ingest.us.sentry.io/4506491534573568';

    /** @var array<string, array<string, mixed>> */
    public const array SERVERS = [];
    /** @var array<string, array<string, string|array<string, string>>> */
    public const array MODULES = [
        'LuckPerms' => [
            'DB_NAME' => 'LuckPerms',
            'prefix' => 'luckperms_',
        ],
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
                'username_column' => 'username',
                'uuid_column' => 'uuid',
                'email_column' => 'email',
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
                /** NULL(int 0)|SLIM(int 1) */
                'texture_meta_column' => 'meta',
            ],
        ],
    ];
}
