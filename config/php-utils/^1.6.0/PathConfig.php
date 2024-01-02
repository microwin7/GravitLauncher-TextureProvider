<?php

namespace Microwin7\PHPUtils\Configs;

/** 
 * Use only from WWW Request
 * @psalm-suppress PossiblyUndefinedArrayOffset
 */
define("DOCUMENT_ROOT", $_SERVER['DOCUMENT_ROOT'] . '/');

class PathConfig
{
    /**
     * WEB адресс приложения
     * Вид: '<http|https>://<IP|IP:PORT|DOMAIN>/'
     * Пример: 'http://127.0.0.1:80/'
     */
    public const string APP_URL = 'http://127.0.0.1:80/';
    /**
     * Укажите root до публичного корня сайта
     * Пример: /var/www/html/
     */
    public const string ROOT_FOLDER = '/var/www/html/';
    /**
     * Логи БД
     */
    public const string DB_LOG_FOLDER = '/var/www/db_logsTextureProvider/';
}
