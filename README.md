# GravitLauncher-TextureProvider (JSON)

![PHP 5.6.0](https://img.shields.io/badge/PHP-5.6.0-blue)
![Gravit Launcher](https://img.shields.io/badge/Gravit%20Launcher-5.2.0-brightgreen)

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
- GravitLauncher 5.2.0+ [#Правки](https://github.com/GravitLauncher/Launcher/compare/fecc14010d30...5d0ccdbde3b9)
- AuthLib под версию 5.2.0 [[СКАЧАТЬ]](https://mirror.gravit.pro/compat/authlib/2/LauncherAuthlib2-5.2.0.jar)
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
    const CLOAK_PATH = "./minecraft-auth/cloaks/"; // Сюда вписать путь до cloaks/
```
`../ - одна директория вверх`
`minecraft-auth папка указана для примера`

- **Настройка отдаваемых ссылок (Не через скрипт)**
```php
    const SKIN_URL = "https://example.com/minecraft-auth/skins/%login%.png";
    const CLOAK_URL = "https://example.com/minecraft-auth/cloaks/%login%.png";
```
Можете спокойно перенести ссылки из уже настроеных в конфиге лаунчсервера, заменив только заполнитель на %login%

- **Настройка отдаваемых ссылок (Через скрипт)**
```php
    const SKIN_URL = "https://example.com/TextureProvider.php?login=%login%";
    const CLOAK_URL = "https://example.com/TextureProvider.php?login=%login%";
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
    "skin": {
        "url": "https://example.com/minecraft-auth/skins/slim.png",
        "digest": "MDk0NTFjMTZjM2EyNzBlZGNhYTUwNzMyYjJjNzNhMzk=",
        "metadata": {
            "model": "slim"
        }
    },
    "cloak": {
        "url": "https://example.com/minecraft-auth/cloaks/slim.png",
        "digest": "ZGM5NGZkNzgyYzBjZmUyNzQ5YTgyNDJhOWI0NDkzNTA="
    }
}
```

- **При наличии только default скина**
```json
{
    "skin": {
        "url": "https://example.com/minecraft-auth/skins/default.png",
        "digest": "YjQ2ZTM4ODljNzBlMGJiOWUyYmExYzdkNGM2ZTI5Zjc="
    }
}
```
