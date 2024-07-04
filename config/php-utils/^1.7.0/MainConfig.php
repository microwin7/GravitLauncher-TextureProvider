<?php

namespace Microwin7\PHPUtils\Configs;

class MainConfig
{
    public const array DB_PDO_OPTIONS = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_PERSISTENT => true
    ];

    /** @var array<string, array<string, mixed>> */
    public const array SERVERS = [];
    /** @var array<string, array<string, string|array<string, string>>> */
    public const array MODULES = [
        'ItemShop' => [
            'DB_NAME' => 'ItemShop',
            'prefix' => ''
        ],
        'VoteRewards' => [
            'DB_NAME' => 'VoteRewards',
            'prefix' => '',
        ],
        'LuckPerms' => [
            'DB_NAME' => 'LuckPerms',
            'prefix' => 'luckperms_',
        ],
        'LiteBans' => [
            'DB_NAME' => 'LiteBans',
            'prefix' => 'litebans_',
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
