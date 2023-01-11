# GravitLauncher-TextureProvider (JSON)

![PHP 7.1+](https://img.shields.io/badge/PHP-7.1+-blue)
![Gravit Launcher](https://img.shields.io/badge/Gravit%20Launcher-5.2.9-brightgreen)

✔ Выдача данных для default (classic) и обнаружение slim скинов.

✔ Работает с любыми общепринятыми размерами скинов и плащей

✔ Может отдавать текстуры Mojang

✔ Выдача скинов и плащей этим скриптом при желании

<p align="center">
    <img src="https://i.imgur.com/q0nkKNj.png" alt="demo" width="642">
</p>

# Поддерживаемые методы

- **`normal`** Отдаёт только по локальному пути или установленный скин и плащ по умолчанию
  - Для отдачи по умолчанию **GIVE_DEFAULT** должен быть включен и отдача текстур должна быть через скрипт
- **`mojang`** Отдаёт текстуры с Mojang
  - Использование в вызове скрипта: **`&method=mojang`**
- **`hybrid`** Отдаёт по локальному пути или с Mojang
  - Для работы необходимо отключить **GIVE_DEFAULT**
  - Использование в вызове скрипта: **`&method=hybrid`**

# Требования

- PHP 7.1+
- GravitLauncher 5.2.9+
- LauncherAuthLib под версию 5.2.9+ [[СКАЧАТЬ]](https://mirror.gravit.pro/5.3.x/compat/authlib/) || AuthLib собранные [[СКАЧАТЬ]](https://mirror.gravit-support.ru/unofficial/authlib/)
- Расширение mbstring `php-mbstring`. Пример для PHP 7.4: `sudo apt-get install php7.4-mbstring`
- Расширение GD `php-gd`. Пример для PHP 7.4: `sudo apt-get install php7.4-gd`

# Установка

- Перейдите в каталог сайта
```bash
curl -O https://raw.githubusercontent.com/microwin7/GravitLauncher-TextureProvider/mojang/TextureProvider.php
```

# Настройка скрипта

- **Настройка пути к скинам и плащам**
```php
    const SKIN_PATH = "./minecraft-auth/skins/"; // Сюда вписать путь до skins/
    const CAPE_PATH = "./minecraft-auth/capes/"; // Сюда вписать путь до capes/
```
`../ - одна директория вверх`
`minecraft-auth папка указана для примера`

- **Настройка отдаваемых текстур (Не через скрипт)**
```php
    const SKIN_URL = "https://example.com/minecraft-auth/skins/%login%.png";
    const CAPE_URL = "https://example.com/minecraft-auth/capes/%login%.png";
```
Можете спокойно перенести ссылки из уже настроеных в конфиге лаунчсервера, заменив только заполнитель на %login%

- **Настройка отдаваемых текстур (Через скрипт)**
```php
    const SKIN_URL = "https://example.com/TextureProvider.php?login=%login%";
    const CAPE_URL = "https://example.com/TextureProvider.php?login=%login%";
```
  - Работает только если ссылки выше указывают на сам скрипт и имеют окончание `?login=%login%`
  - Не используйте с методом `hybrid`, будет выдавать по умолчанию как метод `normal`
```php
    const GIVE_DEFAULT = true;
```

# Настройка TextureProvider в LaunchServer.json

- Запросы по умолчанию, методом **`normal`**
  - **На имени пользователя**
  ```json
  "textureProvider":{
        "url":"https://example.com/TextureProvider.php?login=%username%",
        "type":"json"
     }
  ```
  - **На UUID**
  ```json
  "textureProvider":{
        "url":"https://example.com/TextureProvider.php?login=%uuid%",
        "type":"json"
     }
  ```

- Запрос методом **`mojang`**
  - `Для этого метода не требуется php-gd, информация о slim получается через Mojang API`
  - **На имени пользователя**
  ```json
  "textureProvider":{
        "url":"https://example.com/TextureProvider.php?login=%username%&method=mojang",
        "type":"json"
     }
  ```
  - **На UUID**
  ```json
  "textureProvider":{
        "url":"https://example.com/TextureProvider.php?login=%uuid%&method=mojang",
        "type":"json"
     }
  ```

- Запрос методом **`hybrid`**
  - **На имени пользователя**
  ```json
  "textureProvider":{
        "url":"https://example.com/TextureProvider.php?login=%username%&method=hybrid",
        "type":"json"
     }
  ```
  - **На UUID**
  ```json
  "textureProvider":{
        "url":"https://example.com/TextureProvider.php?login=%uuid%&method=hybrid",
        "type":"json"
     }
  ```

# Примеры ответа в браузере

- **При наличии скина slim и плаща**
```json
{
    "SKIN": {
        "url": "https://example.com/minecraft-auth/skins/slim.png",
        "digest": "MDk0NTFjMTZjM2EyNzBlZGNhYTUwNzMyYjJjNzNhMzk=",
        "metadata": {
            "model": "slim"
        }
    },
    "CAPE": {
        "url": "https://example.com/minecraft-auth/capes/slim.png",
        "digest": "ZGM5NGZkNzgyYzBjZmUyNzQ5YTgyNDJhOWI0NDkzNTA="
    }
}
```

- **При наличии только default скина**
```json
{
    "SKIN": {
        "url": "https://example.com/minecraft-auth/skins/default.png",
        "digest": "YjQ2ZTM4ODljNzBlMGJiOWUyYmExYzdkNGM2ZTI5Zjc="
    }
}
```
