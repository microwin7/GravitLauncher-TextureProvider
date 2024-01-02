# GravitLauncher-TextureProvider (JSON)

![PHP 8.3+](https://img.shields.io/badge/PHP-8.3+-blue)
![Gravit Launcher](https://img.shields.io/badge/Gravit%20Launcher-v5.5.x+-brightgreen)

✔ Выдача по USERNAME, UUID, (id пользователя, хеша sha1 и sha256) из БД.

✔ Поддеркжа выдачи из файловой системы, либо по USERNAME с Mojang

✔ Возможность выдавать рандомный скин пользователям, которые ещё не установили его сами

✔ Выдача скина и плаща по умолчанию, если не обнаружен в файловой системе, Mojang и выключено получение скина из рандомной коллекции скинов

✔ Работает с любыми общепринятыми размерами скинов и плащей

✔ Автоматическое обнаружение SLIM типов скинов (тонкие руки)

<p align="center">
    <img src="https://i.imgur.com/q0nkKNj.png" alt="demo" width="642">
</p>

# Поддерживаемые методы

- **`normal`** Отдаёт только из файловой системы, рандомной коллекции скинов (если оное включено), скинов и плащей по умолчанию.
- **`mojang`** Отдаёт текстуры с Mojang
  - Использование в вызове скрипта: **`&method=mojang`**
- **`hybrid`** = **`normal`** + **`mojang`**
  - Использование в вызове скрипта: **`&method=hybrid`**
- **ОБЩЕЕ**
  - Отдача скинов из рандомной коллекции, при отсутствии установленных пользователями. Если включено
  - Отдача скинов и плащей по умолчанию. Если включено

# Требования

- PHP 8.3+
- GravitLauncher 5.5.x+
- Расширение Multibyte String `php-mbstring`. Пример для PHP 8.3: `sudo apt-get install php8.3-mbstring`
- Расширение GD `php-gd`. Пример для PHP 8.3: `sudo apt-get install php8.3-gd`
- Расширения PDO при работе с БД:
  - Если **DB_DRIVER = 'PDO'** - `mysqli`. Пример для PHP 8.3: `sudo apt-get install php8.3-pdo`
    - **[ MySQL Database ]** Если **DB_SUD_DB = 'mysql'** - `pdo_mysql`. Пример для PHP 8.3: `sudo apt-get install php8.3-pdo_mysql`
      - Аргументы установки с игнорированием `mysqli`, `pdo_pgsql` расширений PHP, так как она не будет использоваться и может не быть в системе:
      - 
        ```bash
        --ignore-platform-req=ext-mysqli --ignore-platform-req=ext-pdo_pgsql
        ```
    - **[ PostgreSQL Database ]** Если **DB_SUD_DB = 'pgsql'** - `pdo_pgsql`. Пример для PHP 8.3: `sudo apt-get install php8.3-pdo_pgsql`
      - Аргументы установки с игнорированием `mysqli`, `pdo_mysql` расширений PHP, так как она не будет использоваться и может не быть в системе:
      -
        ```bash
        --ignore-platform-req=ext-mysqli --ignore-platform-req=ext-pdo_mysql
        ```
- Расширения MySQLi при работе с БД:
  - **[ MySQL Database ]** **DB_DRIVER = 'MySQLi'** - `mysqli`. Поддерживается только **DB_SUD_DB = 'mysql'**. Пример для PHP 8.3: `sudo apt-get install php8.3-mysqli`
    - Аргументы установки с игнорированием `PDO`, `pdo_mysql`, `pdo_pgsql` расширений PHP, так как она не будет использоваться и может не быть в системе:
    - 
      ```bash
      --ignore-platform-req=ext-pdo --ignore-platform-req=ext-pdo_mysql --ignore-platform-req=ext-pdo_pgsql
      ```
- Composer [СКАЧАТЬ Composer](https://getcomposer.org/download/)
- Консольный доступ SSH к хостингу. Для развёртывания библиотек


# Установка

- Перейдите в каталог сайта
  - Установка через Composer:
  ```bash
  composer create-project microwin7/texture-provider
  ```
  - Установка через git:
  ```bash
  git clone --branch new https://github.com/microwin7/GravitLauncher-TextureProvider.git texture-provider
  
  cd texture-provider
  
  composer install
  ```

# Описание TextureStorageType's

1. **STORAGE**
  - Локальное файловое хранилище скинов и плащей
  - Имеет 5 типов для определения имени хранимого файла, они же **StorageType**'s:
    - **USERNAME** - [username.png] (Задан по умолчанию)
      - Поиск происходит вне зависимости от регистра, если файл не будет найден
    - **UUID** - [uuid.png]
    - **DB_USER_ID** - [user_id.png] работает только с связью с БД
    - **DB_SHA1** - [sha1.png] работает только с связью с БД
    - **DB_SHA256** - [sha256.png] работает только с связью с БД
    - Настройка производиться в главном конфиге проекта: `config/texture-provider/Config.php`, константа **USER_STORAGE_TYPE**
    - Для **DB_USER_ID** используется таблица пользователей по UUID, настройка подключения к БД производиться в конфигурации библиотеки:
      - `config/php-utils/^1.5.0/MainConfig.php` константа **MODULES['TextureProvider']**
    - Для **DB_SHA1** и **DB_SHA256** используется таблица **`user_assets`**, поиск записей производиться по UUID.
      - Настройка подключения к БД производиться в конфигурации библиотеки:
        - `config/php-utils/^1.5.0/MainConfig.php` константа **MODULES['TextureProvider']**
      - Для реализации в вашем ЛК:
        - в поле **`uuid`** вы должны записать UUID пользователя
        - в поле **`name`** вы должны записать тип текстуры: **SKIN**, **CAPE**
        - в поле **`hash`** вы должны записать соответствующую хеш сумму файла
        - в поле **`slim`** вы должны записать является ли скин **SLIM**: '1' или 'SLIM' (Да), '0' (Нет)
        - Поддержка поля **`slim`** пока что не реализована
      - Создание таблицы:
      - НЕ АКТУАЛЬНО, будет отредактировано. СМОТРЕТЬ sql/mariadb/ для примера
      ```sql
      CREATE TABLE `user_assets` (
	    `uuid` UUID,
	    `name` ENUM('SKIN','CAPE'),
	    `hash` TINYTEXT NOT NULL,
	    `type` ENUM('SLIM'),
	    PRIMARY KEY (`uuid`, `name`),
	    INDEX `uuid` (`uuid`),
	    INDEX `uuid_name` (`uuid`, `name`)
      );
      ```
      - Этот тип рекомендован. Так же желательно не удалять старые файлы текстур.
      - Позже будет реализован скрипт очистки старых неиспользуемых файлов, с N периодом времени
2. **MOJANG**
  - Поиск текстур в Mojang по **USERNAME**
  - Для использования только этого типа хранения
    - В конце запроса добавьте **`&method=mojang`**
  - Для использования этого типа хранения, вместе со всеми другими
    - В конце запроса добавьте **`&method=hybrid`**
    - Cперва будет поиск по локальному файловому хранилищу, потом Mojang
3. **COLLECTION**
  - Выдава скина из коллекции рандомных скинов, созданную администратором.
  - Последние 12 символов от UUID переводяться в DEC и деляться на количество скинов в коллекции
  - после чего остаток и будет являться номером из коллекции.
  - Смотрите главный конфиг проекта: `config/texture-provider/Config.php`, за коллекции отвечают две константы **GIVE_FROM_COLLECTION** (включить/выключить) и SKIN_RANDOM_COLLECTION_PATH (путь к файлам)
4. **DEFAULT**
  - Выдача скинов и плащей по умолчанию, если не найдены ни в локальном хранилище, ни в Mojang, ни в коллекции скинов.
  - Смотрите главный конфиг проекта: `config/texture-provider/Config.php`, следующие константы **GIVE_DEFAULT_SKIN**, **GIVE_DEFAULT_CAPE**

# Настройка
### Ссылка на скрипт
- Протокол и `ДОМЕН`/`IP` `config/php-utils/^1.5.0/PathConfig.php` константа: **APP_URL**
- Путь на файл **`index.php`** скрипта для запросов `config/php-utils/^1.5.0/MainConfig.php` константа: **SCRIPT_URL**
### Настройка пути корня до сайта
- Путь до корня сайта в конфиге `config/php-utils/^1.5.0/PathConfig.php` константа: **ROOT_FOLDER**
### Локальные хранилища скинов и плащей
- Пути от корня сайта в конфиге `config/php-utils/^1.5.0/TextureConfig.php` константы: **SKIN_URL_PATH** и **CAPE_URL_PATH**
### Настройка LaunchServer
```json
      "textureProvider": {
        "url": "http://127.0.0.1/texture-provider/index.php?username=%username%&uuid=%uuid%",
        "type": "json"
      },
      "mixes": {
        "textureLoader": {
          "urls": {
            "SKIN": "http://127.0.0.1/texture-provider/upload.php?type=SKIN",
            "CAPE": "http://127.0.0.1/texture-provider/upload.php?type=CAPE"
          },
          "slimSupportConf": "SERVER",
          "type": "uploadAsset"
        }
      },
```

##  ...БУДЕТ ДОПОЛНЕНО...

- Предположительно команда для использования на PRODUCTION, будет проверяться
  - Оптимизирует импорты и кеширует классы автозагрузчика, если включен OpCache
composer install -n -v -o -a --no-dev