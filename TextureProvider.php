<?php
#
# Скрипт выдачи скинов и плащей. GravitLauncher 5.2.0+
#
# https://github.com/microwin7/GravitLauncher-TextureProvider
#
header("Content-Type: application/json; charset=UTF-8");

start();
class Constants
{
    const SKIN_PATH = "./minecraft-auth/skins/"; // Сюда вписать путь до skins/
    const CLOAK_PATH = "./minecraft-auth/cloaks/"; // Сюда вписать путь до cloaks/
    const SKIN_URL = "https://example.com/minecraft-auth/skins/%login%.png";
    const CLOAK_URL = "https://example.com/minecraft-auth/cloaks/%login%.png";
    const REGEX_USERNAME = "\w{1,16}$";
    const REGEX_UUID = "[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}";
    public static function getSkinURL($login)
    {
        return str_replace('%login%', $login, self::SKIN_URL);
    }
    public static function getCloakURL($login)
    {
        return str_replace('%login%', $login, self::CLOAK_URL);
    }
    public static function getBase64Encode_MD5($data)
    {
        return base64_encode(md5($data));
    }
}
class Msg
{
    private $msg;
    public function put($key, $data)
    {
        if (!is_null($data)) $this->msg[$key] = $data;
    }
    public function get()
    {
        return $this->msg;
    }
}
class Check
{
    public static function skin($login)
    {
        $loadskin = self::ci_find_file(Constants::SKIN_PATH . $login . '.png');
        if ($loadskin) {
            $msg = array(
                'url' => Constants::getSkinURL($login),
                'digest' => Constants::getBase64Encode_MD5(file_get_contents($loadskin))
            );
            $size = getimagesize($loadskin);
            $fraction = $size[0] / 8;
            $image = imagecreatefrompng($loadskin);
            $x = $fraction * 6.75;
            $y = $fraction * 2.5;
            $rgba = imagecolorat($image, $x, $y);
            if (($rgba & 0x7F000000) >> 24) {
                $msg['metadata'] = array('model' => 'slim');
            }
            return $msg;
        }
    }
    public static function cloak($login)
    {
        $loadskin = self::ci_find_file(Constants::CLOAK_PATH . $login . '.png');
        if ($loadskin) {
            $msg = array(
                'url' => Constants::getCloakURL($login),
                'digest' => Constants::getBase64Encode_MD5(file_get_contents($loadskin))
            );
            return $msg;
        }
    }
    private static function ci_find_file($filename)
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
}
function start()
{
    $login = isset($_GET['login']) ? $_GET['login'] : null;
    regex_valid($login) ?: response();
    $msg = [];
    $skin = Check::skin($login);
    $cloak = Check::cloak($login);
    if (!is_null($skin)) $msg['skin'] = $skin;
    if (!is_null($cloak)) $msg['cloak'] = $cloak;
    response($msg);
}
function response($msg = null)
{
    die(json_encode((object) $msg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function regex_valid($var)
{
    if (!is_null($var) && (preg_match("/^" . Constants::REGEX_USERNAME . "/", $var, $varR) ||
        preg_match("/" . Constants::REGEX_UUID . "/", $var, $varR)))
        return true;
}
