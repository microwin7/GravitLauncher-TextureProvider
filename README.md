# GravitLauncher-TextureProvider (JSON)

![PHP 5.6.0](https://img.shields.io/badge/PHP-5.6.0-blue)
![Gravit Launcher](https://img.shields.io/badge/Gravit%20Launcher-5.2.9-brightgreen)

✔ Выдача данных для default (classic) и обнаружение slim скинов.

✔ Работает с любыми общепринятыми размерами скинов и плащей

✔ Выдача скинов и плащей этим скриптом при желании

<p align="center">
    <img src="https://i.imgur.com/q0nkKNj.png" alt="demo" width="642">
</p>

<h1 align="center">
<br>
Требования
</h1>

- PHP 5.6+
- GravitLauncher 5.2.9+
- LauncherAuthLib под версию 5.2.9+ [[СКАЧАТЬ]](https://mirror.gravit.pro/5.3.x/compat/authlib/) || AuthLib собранные [[СКАЧАТЬ]](https://mirror.gravit-support.ru/unofficial/authlib/)
- Расширение mbstring `php-mbstring`. Пример для PHP 7.4: `sudo apt-get install php7.4-mbstring`
- Расширение GD `php-gd`. Пример для PHP 7.4: `sudo apt-get install php7.4-gd`


<h1 align="center">
<br>
Установка
</h1>

- Перейдите в каталог сайта
```bash
curl -O https://raw.githubusercontent.com/microwin7/GravitLauncher-TextureProvider/main/TextureProvider.php
```

<h1 align="center">
<br>
НАСТРОЙКА
</h1>

- **Настройка пути к скинам и плащам**
```php
    const SKIN_PATH = "./minecraft-auth/skins/"; // Сюда вписать путь до skins/
    const CAPE_PATH = "./minecraft-auth/capes/"; // Сюда вписать путь до capes/
```
`../ - одна директория вверх`
`minecraft-auth папка указана для примера`

- **Настройка отдаваемых ссылок (Не через скрипт)**
```php
    const SKIN_URL = "https://example.com/minecraft-auth/skins/%login%.png";
    const CAPE_URL = "https://example.com/minecraft-auth/capes/%login%.png";
```
Можете спокойно перенести ссылки из уже настроеных в конфиге лаунчсервера, заменив только заполнитель на %login%

- **Настройка отдаваемых ссылок (Через скрипт)**
```php
    const SKIN_URL = "https://example.com/TextureProvider.php?login=%login%";
    const CAPE_URL = "https://example.com/TextureProvider.php?login=%login%";
```
- Работает только если ссылки выше указывают на сам скрипт и имеют окончание `?login=%login%`
```php
    const GIVE_DEFAULT = true;
```

<h1 align="center">
<br>
Настройка textureProvider в LaunchServer.json
</h1>

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

- **Метод получения скинов и плащей через Mojang**
```json
"textureProvider":{
      "url":"https://example.com/TextureProvider.php?login=%username%&method=mojang",
      "type":"json"
   }
```
`работает только на %username%`
`Для этого метода не требуется php-gd, информация о slim получается через Mojang API`
`Без указания метода, будет обычный режим работать`

<h1 align="center">
<br>
Примеры ответа в браузере
</h1>

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
