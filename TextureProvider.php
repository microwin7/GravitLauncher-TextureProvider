<?php
#
# Скрипт выдачи скинов и плащей. GravitLauncher 5.2.0+
#
# https://github.com/microwin7/GravitLauncher-TextureProvider
#
$msg = [];
header("Content-Type: text/plain; charset=UTF-8");
$login = $_GET['login'];

class cfg
{
    static $settings = array(
        "skin_path" => "./minecraft-auth/skins/", // Сюда вписать путь до skins/
        "cloak_path" => "./minecraft-auth/cloaks/", // Сюда вписать путь до cloaks/
        "skinURL" => "http://example.com/skins/%login%.png",
        "cloakURL" => "http://example.com/cloaks/%login%.png",
    );
}
if (strnatcmp(phpversion(), '5.6') >= 0) {
    rgxp_valid($login);
    check_skin($login);
    check_cloak($login);
}
response(true);
function check_skin($login)
{
    global $msg;
    $loadskin = ci_find_file(cfg::$settings['skin_path'] . $login . '.png');  // Поиск пути
    if ($loadskin) {
        $msg['skin'] = array(
            'url' => str_replace('%login%', $login, cfg::$settings['skinURL']),
            'digest' => base64_encode(md5(file_get_contents($loadskin)))
        );
        $size = getimagesize($loadskin); // взятие оригинальных размеров картинки в пикселях
        $fraction = $size[0] / 8;
        $image = imagecreatefrompng($loadskin); // создание png из файла для дальнейшего взаимодействия с ним
        $x1 = $fraction * 6.75;
        $y1 = $fraction * 2.5;
        $rgba = imagecolorat($image, $x1, $y1);
        if (($rgba & 0x7F000000) >> 24) {
            $msg['skin']['metadata'] = array('model' => 'slim');
        }
    }
}
function check_cloak($login)
{
    global $msg;
    $loadskin = ci_find_file(cfg::$settings['cloak_path'] . $login . '.png');  // Поиск пути
    if ($loadskin) {
        $msg['cloak'] = array(
            'url' => str_replace('%login%', $login, cfg::$settings['cloakURL']),
            'digest' => base64_encode(md5(file_get_contents($loadskin)))
        );
    }
    response(true);
}
function rgxp_valid($var)
{
    $pattern_username = '\w{1,16}$';
    $pattern_uuid = "[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}";
    if (
        preg_match("/^" . $pattern_username . "/", $var, $varR) ||
        preg_match("/" . $pattern_uuid . "/", $var, $varR)
    ) {
        return true;
    } else {
        response(true);
    }
}
function response($exit = false)
{
    global $msg;
    if ($exit) die(json_encode((object) $msg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
function ci_find_file($filename)
{
    if (file_exists($filename))
        return $filename;
    $directoryName = dirname($filename);
    $fileArray = glob($directoryName . '/*', GLOB_NOSORT);
    $fileNameLowerCase = strtolower($filename);
    foreach ($fileArray as $file) {
        if (strtolower($file) == $fileNameLowerCase) {
            return $file;
        }
    }
    return false;
}
