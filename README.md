# GravitLauncher-TextureProvider (JSON)

![PHP 8.3+](https://img.shields.io/badge/PHP-8.3+-blue)
![Gravit Launcher](https://img.shields.io/badge/Gravit%20Launcher-v5.2.9+-brightgreen)

✔ Выдача по USERNAME, UUID, (id пользователя, хеша sha1 и sha256) из БД.

✔ Поддеркжа выдачи из файловой системы, либо по USERNAME с Mojang

✔ Возможность выдавать рандомный скин пользователям, которые ещё не установили его сами

✔ Выдача скина и плаща по умолчанию, если не обнаружен в файловой системе, Mojang и выключено получение скина из рандомной коллекции скинов

✔ Работает с любыми общепринятыми размерами скинов и плащей

✔ Автоматическое обнаружение SLIM типов скинов (тонкие руки)

✔ Встроенный TextureLoader

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

- GravitLauncher 5.2.9+
- Консольный доступ SSH к хостингу. Для развёртывания библиотек
### Если не используете Docker:
- PHP 8.3+
- Расширение Multibyte String `php-mbstring`. Пример: `sudo apt-get install php8.3-mbstring`
- Расширение GD `php-gd`. Пример: `sudo apt-get install php8.3-gd`
- Расширения для работы с БД:
  - **[ MySQL Database ]** Если **DB_SUD_DB = 'mysql'** - `mysql`. Пример : `sudo apt-get install php8.3-mysql`
    - Установка с игнорированием `pgsql` расширения PHP, так как оно не будет использоваться в системе:
    - 
      ```bash
      composer install --ignore-platform-req=ext-pgsql
      ```
  - **[ PostgreSQL Database ]** Если **DB_SUD_DB = 'pgsql'** - `pgsql`. Пример: `sudo apt-get install php8.3-pgsql`
    - Установка с игнорированием `mysql` расширения PHP, так как оно не будет использоваться в системе:
    -
      ```bash
      composer install --ignore-platform-req=ext-mysql
      ```
  - Если вы не используете БД, можете игнорировать расширения:
  -
    ```bash
    composer install --ignore-platform-req=ext-mysql --ignore-platform-req=ext-pgsql
    ```
- Composer [Ссылка на иструкцию по установке Composer](https://getcomposer.org/download/)

# Установка
## Установка в Docker контейнер:
<img src="https://img.shields.io/badge/docker-2496ED?style=for-the-badge&logo=docker&logoColor=2496ED&label=%D1%83%D1%81%D1%82%D0%B0%D0%BD%D0%BE%D0%B2%D0%BA%D0%B0%20%D0%B8%20%D0%BD%D0%B0%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B0%20%D1%81%20%D0%BF%D0%BE%D0%BC%D0%BE%D1%89%D1%8C%D1%8E&labelColor=white" alt="Docker" height="50"/>

- Является более оптимальным вариантом установки, так как все модули и сам php будет установлен в изолированных контейнерах

### Предварительная настройка | Установка Docker
<img src="https://img.shields.io/badge/docker-2496ED?style=for-the-badge&logo=docker&logoColor=2496ED&label=%D1%83%D1%81%D1%82%D0%B0%D0%BD%D0%BE%D0%B2%D0%BA%D0%B0&labelColor=white" alt="Docker" height="35"/>

- Выполнение команд от sudo (Перейти в root, если является пользователем не по умолчанию):
```bash
sudo -s
```
- Следующая команда:
  - Обновляет зависимости
  - Установка утилит
  - Скачивает скрипт установки Docker
  - Выдача прав запуска скрипта и запуск установки Docker
  - Запуск службы
```bash
apt update ;
apt install gnupg2 apt-transport-https curl -y;
curl -fsSL https://get.docker.com -o get-docker.sh ;
chmod +x get-docker.sh ;
./get-docker.sh ;
service docker start
```
<img src="https://img.shields.io/badge/git-F05032?style=for-the-badge&logo=github&logoColor=181717&label=%D0%9A%D0%BB%D0%BE%D0%BD%D0%B8%D1%80%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D0%B5%20%D1%80%D0%B5%D0%BF%D0%BE%D0%B7%D0%B8%D1%82%D0%BE%D1%80%D0%B8%D1%8F&labelColor=white" alt="Git" height="35"/>

### Выбираем где будет располагаться скрипт, лучше всего вне сайта. И устанавливаем texture-provider
```bash
git clone --branch new https://github.com/microwin7/GravitLauncher-TextureProvider.git texture-provider
```
```bash
cd texture-provider
```
<img src="https://img.shields.io/badge/docker-2496ED?style=for-the-badge&logo=docker&logoColor=2496ED&label=%D0%98%D0%BD%D0%B8%D1%86%D0%B8%D0%B0%D0%BB%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F%20%D0%BA%D0%BE%D0%BD%D1%82%D0%B5%D0%B9%D0%BD%D0%B5%D1%80%D0%BE%D0%B2&labelColor=white" alt="Docker" height="35"/>

### Скачивание слоёв, компиляция и запуск контейнеров php-fpm и nginx:
```bash
docker compose up -d --build
```
### Остановка контейнеров:
```bash
docker compose stop
```
### Запуск контейнеров / Перезапуск (если изменился файл docker-compose.yml):
```bash
docker compose up -d
```
### <img src="https://img.shields.io/badge/NGINX-009639?style=for-the-badge&logo=nginx&logoColor=009639&label=%D0%9D%D0%90%D0%A1%D0%A2%D0%A0%D0%9E%D0%99%D0%9A%D0%90%20%D0%B2&labelColor=white" alt="NGINX" height="35"/>

#### Для установки на сайт:
- Над разделом server {...}
```nginx
upstream dockerTextureProvider {
    server 127.0.0.1:29300;
}
```
- А в разделе server {...}
```nginx
    location /texture-provider/ {
        proxy_pass http://dockerTextureProvider/;
    }
```
- Изменить **APP_URL** в `.env` - ссылка на домен
#### Для установки на под-домен:
- Пример **`/etc/nginx/conf.d/textures.conf`**:
```nginx
upstream dockerTextureProvider {
    server 127.0.0.1:29300;
}
server {
    listen 80;
    server_name textures.ВАШ_ДОМЕН;
    charset utf-8;

    location / {
        proxy_pass http://dockerTextureProvider/;
    }
}
```
- Изменить **ВАШ_ДОМЕН**
- Изменить **APP_URL** в `.env` - ссылка на домен
- Выставить **SCRIPT_PATH=** пустым полем в `.env` - ссылка для обращения к провидеру, по отношениею к конрю сайта
- Подпись домена вы можете выполнить через [**[ CertBot ]**](https://certbot.eff.org/)
#### Перезагрузить NGINX
```bash
service nginx restart
```
## Использование Composer | ❗❗❗ Не рекомендуется для неопытных пользователей ❗❗❗
<img src="https://img.shields.io/badge/composer-F28D1A?style=for-the-badge&logo=packagist&logoColor=gray&label=Packagist&labelColor=white" alt="Composer" height="50"/>

- Composer [Ссылка на иструкцию по установке Composer](https://getcomposer.org/download/)
- ❗❗❗ **Для использования у вас уже должен быть установлен php со всеми необходимыми модулями**
```bash
composer create-project microwin7/texture-provider
```
- Для инициализации всех пакетов, используется команда:
```bash
composer install
```

# НАСТРОЙКА СКРИПТА
## Описание TextureStorageType's

1. **STORAGE**
  - Локальное файловое хранилище скинов и плащей
  - Имеет 5 типов для определения имени хранимого файла, они же **StorageType**'s:
    - **USERNAME** - [username.png] (Задан по умолчанию)
      - Поиск происходит вне зависимости от регистра, если файл не будет найден
    - **UUID** - [uuid.png]
    - **DB_USER_ID** - [user_id.png] работает только с связью с БД
    - **DB_SHA1** - [sha1.png] работает только с связью с БД
    - **DB_SHA256** - [sha256.png] работает только с связью с БД
    - Настройка в конфиге`.env`: `USER_STORAGE_TYPE`
    - Для **DB_USER_ID** используется таблица пользователей по UUID, настройка подключения к БД производиться в конфигурации библиотеки:
      - `config/php-utils/^1.7.0/MainConfig.php` константа **MODULES['TextureProvider']**
    - Для **DB_SHA1** и **DB_SHA256** используется таблица **`user_assets`**, поиск пользователя производиться по UUID в таблице пользователей, запись в эту таблицу осуществляется с привязыванием к id колонке пользователя
      - Настройка подключения к БД производиться в конфигурации библиотеки:
        - `config/php-utils/^1.7.0/MainConfig.php` константа **MODULES['TextureProvider']**
      - Для реализации в вашем ЛК:
        - в поле **`uuid`** вы должны записать UUID пользователя
        - в поле **`name`** вы должны записать тип текстуры: **SKIN**, **CAPE**
        - в поле **`hash`** вы должны записать соответствующую хеш сумму файла
        - в поле **`slim`** вы должны записать является ли скин **SLIM**: '1' или 'SLIM' (Да), '0' (Нет)
        - Поддержка поля **`slim`** пока что не реализована
      - Создание таблицы для хранения хешей:
      ```sql
      CREATE TABLE user_assets (
	      user_id INT,
	      type ENUM('SKIN','CAPE'),
	      hash TINYTEXT NOT NULL,
	      meta ENUM('SLIM'),
	    PRIMARY KEY (user_id, type),
	    INDEX uid (user_id),
	    INDEX uid_name (user_id, type),
	    FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE CASCADE
	    );
      ```
      - Изменить имя таблицы и колонки id с ключом для связывания с `PRIMARY KEY` в этой части: `REFERENCES users(user_id)`
      - Этот тип рекомендован. Так же желательно не удалять старые файлы текстур.
      - Позже будет реализован скрипт очистки старых неиспользуемых файлов, с N периодом времени
1. **MOJANG**
  - Поиск текстур в Mojang по **USERNAME**
  - Для использования только этого типа хранения
    - В конце запроса добавьте **`&method=mojang`**
  - Для использования этого типа хранения, вместе со всеми другими
    - В конце запроса добавьте **`&method=hybrid`**
    - Cперва будет поиск по локальному файловому хранилищу, потом Mojang
2. **COLLECTION**
  - Выдава скина из коллекции рандомных скинов, созданную администратором.
  - Последние 12 символов от UUID переводяться в DEC и деляться на количество скинов в коллекции
  - после чего остаток и будет являться номером из коллекции.
  - Включение хранилища в `.env`: **GIVE_FROM_COLLECTION=true**
3. **DEFAULT**
  - Выдача скинов и плащей по умолчанию, если не найдены ни в локальном хранилище, ни в Mojang, ни в коллекции скинов.
  - Включение в `.env`: **GIVE_DEFAULT_SKIN=true** и **GIVE_DEFAULT_CAPE=true**. По умолчанию скины отдаются всегда

## Ссылка на скрипт
- Протокол и `ДОМЕН`/`IP` `.env` константа: **APP_URL**
- Путь от корня домена `.env` константа: **SCRIPT_PATH**. Сделайть пустой **SCRIPT_PATH=** если используете под-домен
## Настройка пути корня до сайта
- Путь до корня сайта или texture-provider'a в конфиге `.env` константа: **ROOT_FOLDER**. По умолчанию: /var/www/html
## Хранилище текстур
- Хранилище от корня сайта в конфиге `.env`: **STORAGE_DIR**. По умолчанию: storage
### Если у вас своя папка storage
  - Удалите текущую папку storage
  ```bash
  rm -rf storage
  ```
  - Создайте ссылку на папку storage. ПРИМЕР для Azuriom:
  ```bash
  ln -s /var/www/html/Azuriom_SITE/storage/app/public storage
  ```
- Пути от корня хранилища в конфиге `.env`: **TEXTURE_{ТИП_ТЕКСТУРЫ}_PATH**. Примеры есть в `.env.example`
### Для включения поддержки версий 5.2.9-5.4.x
- Включите изменение хеша для старых версий в `.env`: **LEGACY_DIGEST**
## Настройка NGINX (ТОЛЬКО ДЛЯ ТЕХ КТО НЕ ИСПОЛЬЗУЕТ DOCKER)
### На домен example.com/texture-provider/:
```nginx
    location /texture-provider/ {
        rewrite "^(/texture-provider)/(.*)$" $1/index.php?$2 last;
        alias /var/www/html/texture-provider/public/;
        location ~ \.php$ {
            fastcgi_pass unix:/run/php/php8.3-fpm.sock;
            fastcgi_buffering off;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            include         /etc/nginx/fastcgi_params;
        }
    }
```
### На СУБ-домен (под-домен):
```nginx
server {
    listen 80;
    server_name textures.ВАШ_ДОМЕН;
    charset utf-8;

    #access_log  /var/log/nginx/texture-provider.access.log;
    #error_log  /var/log/nginx/texture-provider.error.log notice;

    root /путь/до/public; # Example: /var/www/html/texture-provider/public

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location / {
        rewrite "^/(.*)$" /index.php?$1 last;
        location ~ \.php$ {
            fastcgi_pass unix:/run/php/php8.3-fpm.sock;
            fastcgi_index index.php;
            fastcgi_buffering off;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include         /etc/nginx/fastcgi_params;
        }
    }
}
```
- **alias** путь заменить на путь, где располагается папка public/, слеш в конце обязателен

## Настройка LaunchServer'a
### При использовании в сайте:
```json
      "textureProvider": {
        "url": "https://example.com/texture-provider/%username%/%uuid%",
        "type": "json"
      },
      "mixes": {
        "textureLoader": {
          "urls": {
            "SKIN": "https://example.com/texture-provider/upload/SKIN",
            "CAPE": "https://example.com/texture-provider/upload/CAPE"
          },
          "slimSupportConf": "SERVER",
          "type": "uploadAsset"
        }
      },
```
### При использовании на под-домене:
```json
      "textureProvider": {
        "url": "https://textures.example.com/%username%/%uuid%",
        "type": "json"
      },
      "mixes": {
        "textureLoader": {
          "urls": {
            "SKIN": "https://textures.example.com/upload/SKIN",
            "CAPE": "https://textures.example.com/upload/CAPE"
          },
          "slimSupportConf": "SERVER",
          "type": "uploadAsset"
        }
      },
```
## Настройка публичного ключа доступа для загрузки скинов и плащей из лаунчера:
- Перейдите в папку лаунчсервера, далее в папку `.keys`. Она может быть скрыта
- Скопируйте себе на ПК файл `ecdsa_id.pub`
- Через сайт [**[ base64.guru ]**](https://base64.guru/converter/encode/file) преобразуйте файл в строку Base64
- В файле `.env` в корне текстур провидера, выставите переменную, по примеру:
```env
LAUNCH_SERVER_ECDSA256_PUBLIC_KEY_BASE64=MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEJDi51DKs5f6ERSrDDjns00BkI963L9OS9wLA2Ak/nACZCgQma+FsTsbYtZQm4nk+rtabM8b9JgzSi3sPINb8fg==
```

# Описание глобальных переменных для настройки скрипта
- Все доступные параметры находятся в `.env.example` - файл пример, с значениями по умолчанию в текстур провидере и библиотеке. За исключением:
  - `config/php-utils/^1.7.0/MainConfig.php` в котором осталась:
    - Настройка баз данных и таблиц с именами столбцов
    - Настройка списка серверов (Не требуется для текстур провидера)
    - Настройка параметров подключения к бд
  - `config/php-utils/^1.7.0/TextureConfig.php` в котором настраивается:
    - **SKIN_SIZE** - доступные размеры для загрузки обычный скинов
    - **CAPE_SIZE** - доступные размеры для загрузки обычный плащей
    - **SKIN_SIZE_HD** - доступные размеры для загрузки HD скинов
    - **CAPE_SIZE_HD** - доступные размеры для загрузки HD плащей
  - P.S. При использовании Docker, после изменений конфигов, помимо `.env` выполните `up -d --build` снова
```env
## Global Settings
APP_URL=https://gravit-support.ru/ - Ссылка на сайт
ROOT_FOLDER=/var/www/html - Указания корня сайта/корня текстур провидера
SCRIPT_PATH=texture-provider - Указание URL location для ссылок при генерации JSON для лаунчсервера(лаунчера)
## DataBase Settings
DB_HOST=localhost - Хост базы данных (БД)
DB_NAME=test - Имя базы данных
DB_USER=test - Имя пользователя для подключения к БД
DB_PASS=test - Имя пользователя для подключения к БД
# 0-65535 - допустимые порты
DB_PORT=3306 - Порт для подключения к БД
# mysql/pgsql
DB_SUD_DB=mysql - Тип драйвера для работы с БД
DB_PREFIX_SERVERS=server_ - Префикс для баз данных серверов (Не используется для текстур провидера)
DB_DEBUG=true - Включение записи логов запросов и ошибок, которые вызываются в скрипте и поступают в БД
# Logs SQL and Errors
DB_LOG_FOLDER=/var/www/db_logs - Путь для хранения логов

BEARER_TOKEN=null - Токен доступа для ограничения запросов (Выключено)
PRIVATE_API_KEY= - Похожее что и выше (Не используется для текстур провидера)

## SENTRY Settings
SENTRY_ENABLE=true
SENTRY_DSN=https://dce66e1d574a0066589421bda5c36b2f@o484368.ingest.us.sentry.io/4506491534573568

LAUNCH_SERVER_ECDSA256_PUBLIC_KEY_BASE64=MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEJDi51DKs5f6ERSrDDjns00BkI963L9OS9wLA2Ak/nACZCgQma+FsTsbYtZQm4nk+rtabM8b9JgzSi3sPINb8fg==

## ItemShop - Для модуля на сайт DLE (Не используется для текстур провидера)
SITE_TEMPLATES_FOLDER=templates/имя_шаблона
ITEM_SHOP_PATH_IN_TEMPLATES=images/item_shop

## Storage Textures - Настройка Хранилища для текстур провидера
STORAGE_DIR=storage
# SKIN|CAPE|AVATAR|FRONT|FRONT_CAPE|FRONT_WITH_CAPE|BACK|
# BACK_CAPE|BACK_WITH_CAPE|CAPE_RESIZE|MOJANG|COLLECTION
TEXTURE_SKIN_PATH=skins
TEXTURE_CAPE_PATH=capes
TEXTURE_AVATAR_PATH=avatars
TEXTURE_FRONT_PATH=fronts
TEXTURE_FRONT_CAPE_PATH=front_capes
TEXTURE_FRONT_WITH_CAPE_PATH=front_with_capes
TEXTURE_BACK_PATH=backs
TEXTURE_BACK_CAPE_PATH=back_capes
TEXTURE_BACK_WITH_CAPE_PATH=back_with_capes
TEXTURE_CAPE_RESIZE_PATH=cape_resizes
TEXTURE_MOJANG_PATH=mojang
TEXTURE_COLLECTION_PATH=collection
# .png
TEXTURE_EXTENSTION=png - Расширения для хранимых файлов

LEGACY_DIGEST=false - Подпись хеш-сумма файлов, старого образца с версии 5.2.9 до 5.4.x
MAX_SIZE_BYTES=2M - Максимальный размер загружаемого файла, так же изменить в nginx и php-fpm контейнере, если используете Docker. В папке config/
# Скин в формате Base64 для DEFAULT хранилища (Посетите сайт https://base64.guru/converter/encode/image)
SKIN_DEFAULT=iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgCAMAAACVQ462AAAAWlBMVEVHcEwsHg51Ri9qQC+HVTgjIyNOLyK7inGrfWaWb1udZkj///9SPYmAUjaWX0FWScwoKCgAzMwAXl4AqKgAaGgwKHImIVtGOqU6MYkAf38AmpoAr68/Pz9ra2t3xPtNAAAAAXRSTlMAQObYZgAAAZJJREFUeNrUzLUBwDAUA9EPMsmw/7jhNljl9Xdy0J3t5CndmcOBT4Mw8/8P4pfB6sNg9yA892wQvwzSIr8f5JRzSeS7AaiptpxazUq8GPQB5uSe2DH644GTsDFsNrqB9CcDgOCAmffegWWwAExnBrljqowsFBuGYShY5oakgOXs/39zF6voDG9r+wLvTCVUcL+uV4m6uXG/L3Ut691697tgnZgJavinQHOB7DD8awmaLWEmaNuu7YGf6XcIITRm19P1ahbARCRGEc8x/UZ4CroXAQTVIGL0YySrREBADFGicS8XtG8CTS+IGU2F6EgSE34VNKoNz8348mzoXGDxpxkQBpg2bWobjgZSm+uiKDYH2BAO8C4YBmbgAjpq5jUl4yGJC46HQ7HJBfkeTAImIEmgmtpINi44JsHx+CKA/BTuArISXeBTR4AI5gK4C2JqRfPs0HNBkQnG8S4Yxw8IGoIZfXEBOW1D4YJDAdNSXgRevP+ylK6fGBCwsWywmA19EtBkJr8K2t4N5pnAVwH0jptsBp+2gUFj4tL5ywAAAABJRU5ErkJggg==
# Плащ в формате Base64 для DEFAULT хранилища (Посетите сайт https://base64.guru/converter/encode/image)
CAPE_DEFAULT=iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgAQMAAACYU+zHAAAAA1BMVEVHcEyC+tLSAAAAAXRSTlMAQObYZgAAAAxJREFUeAFjGAV4AQABIAABL3HDQQAAAABJRU5ErkJggg==

## Texture-Provider - Настройки провидера (Описание можно найти в файле src/Config.php)
ROUTERING=true
MINIMIZE_ENUM_REQUEST=false
# null|80-512 Default: 128px
AVATAR_CANVAS=null
# USERNAME - [username.png]
# UUID - [uuid.png]
# DB_USER_ID - [user_id.png] работает только с связью с БД
# DB_SHA1 - [sha1.png] работает только с связью с БД
# DB_SHA256 - [sha256.png] работает только с связью с БД
USER_STORAGE_TYPE=UUID
GIVE_FROM_COLLECTION=false
TRY_REGENERATE_CACHE=true
GIVE_DEFAULT_SKIN=true
GIVE_DEFAULT_CAPE=false
SKIN_RESIZE=true
### Texture-Provider Loader
HD_TEXTURES_ALLOW=true
LUCKPERMS_USE_PERMISSION_HD_SKIN=false
# Min 0
LUCKPERMS_MIN_WEIGHT=10
### Texture-Provider Returner
BLOCK_CANVAS=128
CAPE_CANVAS=16
BOUND_WIDTH_CANVAS=512
# Min 10 sec
IMAGE_CACHE_TIME=null
```
# ПРОЧЕЕ...
### Удаление чересстрочной развёртки. И сжатие
Предупреждения:
- (Эти предупреждения связаны с библиотекой libpng, которая используется для работы с изображениями в формате PNG)
1. `libpng warning: Interlace handling should be turned on when using png_read_image` - Это предупреждение говорит о том, что вам следует включить межстрочное сканирование (interlace) при использовании функции `png_read_image`. Межстрочное сканирование позволяет пошагово загружать изображение, что может быть полезным для оптимизации процесса отображения.

Способ починить:
```bash
apt install optipng
```
- Команды приведены от самой долгой по обратоке к самой быстрой
- Вызывать в папке с скинами или плащами
```bash
find . -type f -iname '*.png' -exec optipng -i0 -o7 -zm1-9 {} \;
find . -type f -iname '*.png' -exec optipng -i0 -o1 -zm1-9 {} \;
find . -type f -iname '*.png' -exec optipng -i0 -o1 {} \;
```

### Починка профиля изображения
Предупреждения:
- (Эти предупреждения связаны с библиотекой libpng, которая используется для работы с изображениями в формате PNG)
1. `libpng warning: iCCP: known incorrect sRGB profile` - Это предупреждение указывает на то, что профиль цвета sRGB в изображении не соответствует ожидаемому или считается некорректным. Возможно, изображение содержит нестандартный профиль цвета, и это может повлиять на отображение цветов.

2. `libpng warning: iCCP: cHRM chunk does not match sRGB` - Это предупреждение также связано с профилем цвета и указывает на то, что информация о цветовом пространстве (cHRM chunk) не соответствует ожидаемому для sRGB. Это может также привести к неправильному отображению цветов.

Способ починить:
```bash
apt install pngcrush
```
- Вызывать в папке с скинами или плащами
```bash
find . -type f -iname '*.png' -exec pngcrush -ow -rem allb -reduce {} \;
```

##  ...БУДЕТ ДОПОЛНЕНО...

- Предположительно команда для использования на PRODUCTION, будет проверяться
  - Оптимизирует импорты и кеширует классы автозагрузчика, если включен OpCache
composer install -n -v -o -a --no-dev