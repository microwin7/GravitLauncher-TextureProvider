<?php

namespace Microwin7\PHPUtils\Configs;

class PathConfig
{
    /**
     * WEB адресс приложения
     * Вид: '<http|https>://<IP|IP:PORT|DOMAIN>/'
     * Пример: 'http://127.0.0.1:80/'
     */
    public const APP_URL = 'http://127.0.0.1:80/';
    /**
     * Укажите root до публичного корня сайта
     * Пример: /var/www/html/
     */
    public const ROOT_FOLDER = '/var/www/html/';
    /**
     * Логи БД
     */
    public const DB_LOG_FOLDER = '/var/www/db_logsTextureProvider/';
}
