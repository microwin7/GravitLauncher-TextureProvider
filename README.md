# GravitLauncher-TextureProvider (JSON)

![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-blue)
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

- PHP 8.2+
- GravitLauncher 5.5.x+
- Расширение Multibyte String `php-mbstring`. Пример для PHP 8.2: `sudo apt-get install php8.2-mbstring`
- Расширение GD `php-gd`. Пример для PHP 8.2: `sudo apt-get install php8.2-gd`
- Расширения PDO при работе с БД:
  - Если **DB_DRIVER = 'PDO'** - `mysqli`. Пример для PHP 8.2: `sudo apt-get install php8.2-pdo`
    - **[ MySQL Database ]** Если **DB_SUD_DB = 'mysql'** - `pdo_mysql`. Пример для PHP 8.2: `sudo apt-get install php8.2-pdo_mysql`
      - Аргументы установки с игнорированием `mysqli`, `pdo_pgsql` расширений PHP, так как она не будет использоваться и может не быть в системе:
      - 
        ```bash
        --ignore-platform-req=ext-mysqli --ignore-platform-req=ext-pdo_pgsql
        ```
    - **[ PostgreSQL Database ]** Если **DB_SUD_DB = 'pgsql'** - `pdo_pgsql`. Пример для PHP 8.2: `sudo apt-get install php8.2-pdo_pgsql`
      - Аргументы установки с игнорированием `mysqli`, `pdo_mysql` расширений PHP, так как она не будет использоваться и может не быть в системе:
      -
        ```bash
        --ignore-platform-req=ext-mysqli --ignore-platform-req=ext-pdo_mysql
        ```
- Расширения MySQLi при работе с БД:
  - **[ MySQL Database ]** **DB_DRIVER = 'MySQLi'** - `mysqli`. Поддерживается только **DB_SUD_DB = 'mysql'**. Пример для PHP 8.2: `sudo apt-get install php8.2-mysqli`
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
  ...БУДЕТ ДОПОЛНЕНО...
  ```
  - Установка через git:
  ```bash
  git clone --branch new https://github.com/microwin7/GravitLauncher-TextureProvider.git
  ```
  ```bash
  cd GravitLauncher-TextureProvider
  ```
  ```bash
  composer install
  ```

##  ...БУДЕТ ДОПОЛНЕНО...