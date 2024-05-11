<?php

namespace Microwin7\TextureProvider;

use Microwin7\PHPUtils\Contracts\User\UserStorageTypeEnum;

class Config
{
    /**
     * Включение переопределения ссылок
     * Пример:
     * c: index.php?username=microwin7&uuid=973ae46f-897c-11ee-93f2-ac1f6bc5d1c6
     * на: microwin7/973ae46f-897c-11ee-93f2-ac1f6bc5d1c6
     * На данный момент все правила изменяются только через nginx
     * Используйте конфигурацию location /texture-provider/ из README
     */
    private const bool ROUTERING = true;
    /**
     * Небольшое дополнение, преобразует отдаваемые ссылки
     * Там где используется ENUM тип int, происходит переключение с имени Enum на значение
     * Пример:
     * с: texture-provider/SKIN/STORAGE/c6bddeda3e68acbb048f3961108a56bdb6b1397b0f56e72804f20b52ad19f5b8
     * на: texture-provider/1/0/c6bddeda3e68acbb048f3961108a56bdb6b1397b0f56e72804f20b52ad19f5b8
     * Служит только для укорачивания длины ссылки
     */
    private const bool MINIMIZE_ENUM_REQUEST = false;
    /**
     * Ширина и высота для аватара (два слоя головы)
     * Если будут проблемы с HD скинами с шириной 1024, установить значение на 128
     * Может быть null, будет использоваться значение BLOCK_CANVAS, а указание размера пропадёт из генерируемых ссылок
     */
    private const ?int AVATAR_CANVAS = null;
    /**
     * Тип имени файлов для StorageType
     * USERNAME - [username.png]
     * UUID - [uuid.png]
     * DB_USER_ID - [user_id.png] работает только с связью с БД
     * DB_SHA1 - [sha1.png] работает только с связью с БД
     * DB_SHA256 - [sha256.png] работает только с связью с БД
     */
    private const UserStorageTypeEnum USER_STORAGE_TYPE = UserStorageTypeEnum::UUID;

    /**
     * Выдавать ли рандомный скин из коллекции?
     * Действует только на скины.
     * Приоритенее чем GIVE_DEFAULT_SKIN для скина
     */
    private const bool GIVE_FROM_COLLECTION = false;
    /**
     * При несоответсвтии хеш суммы файла в кеше,
     * либо при ненахождение файла из кеша в файловой системе,
     * сколько раз пытаться перегенерировать кеш и повторить вызов метода?
     */
    private const bool TRY_REGENERATE_CACHE = true;
    /**
     * Выдавать ли default скины, если они не обнаружены в других источниках?
     */
    private const bool GIVE_DEFAULT_SKIN = true; // Выдавать ли этим скриптом default скины.
    /**
     * Выдавать ли default плащи, если они не обнаружены в других источниках?
     */
    private const bool GIVE_DEFAULT_CAPE = false;

    private const bool SKIN_RESIZE = true;  // Скины преобразовываются на лету, копируя руку и ногу, для новых форматов. Чинит работу HD скинов с оптифайном на 1.16.5 версии
    // Для 1.7.10 требуется SkinPort (https://github.com/RetroForge/SkinPort/releases)

    /**
     * Разрешить загружать HD скины и плащи по умолчанию через API для лаунчера
     * При включении USE_LUCKPERMS_PERMISSION_HD_SKIN - отключить
     */
    private const bool HD_TEXTURES_ALLOW = true;
    /**
     * Ограничение для загрузки HD скинов и плащей по правам LuckPerms
     * При использовании, обязательно настроить подключение к БД в MainConfig
     */
    private const bool LUCKPERMS_USE_PERMISSION_HD_SKIN = false;
    /**
     * Минимальный веса группы пользователя из LuckPerms для предоставления права устанавливать HD текстуры
     * Число должно быть больше 0, 0 - группа по умолчанию, ограничивать не имеет смысла
     */
    private const int LUCKPERMS_MIN_WEIGHT = 10;
    /** Ширина блока для таких методов как front, back, применяется к avatar, если размер CANVAS указан как null */
    private const int BLOCK_CANVAS = 128;
    /** Умножится на минимальную ширину в 22 пикселя */
    private const int CAPE_CANVAS = 16;
    /**
     * Предел ширины, для ограничения нагрузки вызываемого параметра size
     * Действует только на front, back, avatar, cape_resize == элементы работающие с canvas полем и преобразованием размера
     */
    private const int BOUND_WIDTH_CANVAS = 512;
    /**
     * Кеширование front, back, avatar, cape_resize в секундах
     * Выставить 60, если используется хранение по username, uuid, user_id, а не хеш сумме
     */
    private const ?int IMAGE_CACHE_TIME = 60;

    public static function ROUTERING(): bool
    {
        return ($ENV = getenv(__FUNCTION__)) === false ? self::ROUTERING : filter_var($ENV, FILTER_VALIDATE_BOOLEAN);
    }
    public static function MINIMIZE_ENUM_REQUEST(): bool
    {
        return ($ENV = getenv(__FUNCTION__)) === false ? self::MINIMIZE_ENUM_REQUEST : filter_var($ENV, FILTER_VALIDATE_BOOLEAN);
    }
    public static function AVATAR_CANVAS(): ?int
    {
        return ($ENV = getenv(__FUNCTION__)) === false ?
            self::AVATAR_CANVAS : (
                strtolower($ENV) === 'null' ? null : (
                    ($ENV_INT = filter_var($ENV, FILTER_VALIDATE_INT, ['options' => ['min_range' => 80, 'max_range' => 512]])) === false ?
                    throw new \RuntimeException(sprintf('Invalid value set in environment %s: %s', __FUNCTION__, $ENV)) :
                    $ENV_INT)
            );
    }
    /** @throws \RuntimeException */
    public static function USER_STORAGE_TYPE(): UserStorageTypeEnum
    {
        return ($ENV = getenv(__FUNCTION__)) === false ?
            self::USER_STORAGE_TYPE : (
                ($ENUM = UserStorageTypeEnum::tryFromString($ENV)) === null ?
                throw new \RuntimeException(sprintf('Invalid value set in environment %s: %s', __FUNCTION__, $ENV)) :
                $ENUM
            );
    }
    public static function GIVE_FROM_COLLECTION(): bool
    {
        return ($ENV = getenv(__FUNCTION__)) === false ? self::GIVE_FROM_COLLECTION : filter_var($ENV, FILTER_VALIDATE_BOOLEAN);
    }
    public static function TRY_REGENERATE_CACHE(): bool
    {
        return ($ENV = getenv(__FUNCTION__)) === false ? self::TRY_REGENERATE_CACHE : filter_var($ENV, FILTER_VALIDATE_BOOLEAN);
    }
    public static function GIVE_DEFAULT_SKIN(): bool
    {
        return ($ENV = getenv(__FUNCTION__)) === false ? self::GIVE_DEFAULT_SKIN : filter_var($ENV, FILTER_VALIDATE_BOOLEAN);
    }
    public static function GIVE_DEFAULT_CAPE(): bool
    {
        return ($ENV = getenv(__FUNCTION__)) === false ? self::GIVE_DEFAULT_CAPE : filter_var($ENV, FILTER_VALIDATE_BOOLEAN);
    }
    public static function SKIN_RESIZE(): bool
    {
        return ($ENV = getenv(__FUNCTION__)) === false ? self::SKIN_RESIZE : filter_var($ENV, FILTER_VALIDATE_BOOLEAN);
    }
    public static function HD_TEXTURES_ALLOW(): bool
    {
        return ($ENV = getenv(__FUNCTION__)) === false ? self::HD_TEXTURES_ALLOW : filter_var($ENV, FILTER_VALIDATE_BOOLEAN);
    }
    public static function LUCKPERMS_USE_PERMISSION_HD_SKIN(): bool
    {
        return ($ENV = getenv(__FUNCTION__)) === false ? self::LUCKPERMS_USE_PERMISSION_HD_SKIN : filter_var($ENV, FILTER_VALIDATE_BOOLEAN);
    }
    public static function LUCKPERMS_MIN_WEIGHT(): int
    {
        return ($ENV = getenv(__FUNCTION__)) === false ?
            self::LUCKPERMS_MIN_WEIGHT : (
                ($ENV_INT = filter_var($ENV, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]])) === false ?
                throw new \RuntimeException(sprintf('Invalid value set in environment %s: %s', __FUNCTION__, $ENV)) :
                $ENV_INT
            );
    }
    public static function BLOCK_CANVAS(): int
    {
        return ($ENV = getenv(__FUNCTION__)) === false ?
            self::BLOCK_CANVAS : (
                ($ENV_INT = filter_var($ENV, FILTER_VALIDATE_INT, ['options' => ['min_range' => 128]])) === false ?
                throw new \RuntimeException(sprintf('Invalid value set in environment %s: %s', __FUNCTION__, $ENV)) :
                $ENV_INT
            );
    }
    public static function CAPE_CANVAS(): int
    {
        return ($ENV = getenv(__FUNCTION__)) === false ?
            self::CAPE_CANVAS : (
                ($ENV_INT = filter_var($ENV, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) === false ?
                throw new \RuntimeException(sprintf('Invalid value set in environment %s: %s', __FUNCTION__, $ENV)) :
                $ENV_INT
            );
    }
    public static function BOUND_WIDTH_CANVAS(): int
    {
        return ($ENV = getenv(__FUNCTION__)) === false ?
            self::BOUND_WIDTH_CANVAS : (
                ($ENV_INT = filter_var($ENV, FILTER_VALIDATE_INT, ['options' => ['min_range' => 128, 'max_range' => 896]])) === false ?
                throw new \RuntimeException(sprintf('Invalid value set in environment %s: %s', __FUNCTION__, $ENV)) :
                $ENV_INT
            );
    }
    public static function IMAGE_CACHE_TIME(): ?int
    {
        return ($ENV = getenv(__FUNCTION__)) === false ?
            self::IMAGE_CACHE_TIME : (
                strtolower($ENV) === 'null' ? null : (
                    ($ENV_INT = filter_var($ENV, FILTER_VALIDATE_INT, ['options' => ['min_range' => 10]])) === false ?
                    throw new \RuntimeException(sprintf('Invalid value set in environment %s: %s', __FUNCTION__, $ENV)) :
                    $ENV_INT)
            );
    }
}
