<?php
#
# Скрипт выдачи скинов и плащей. GravitLauncher 5.2.0+
#
# https://github.com/microwin7/GravitLauncher-TextureProvider
#
start();
class Constants
{
    const SKIN_PATH = "./minecraft-auth/skins/"; // Сюда вписать путь до skins/
    const CLOAK_PATH = "./minecraft-auth/cloaks/"; // Сюда вписать путь до cloaks/
    const SKIN_URL = "https://example.com/minecraft-auth/skins/%login%.png";
    const CLOAK_URL = "https://example.com/minecraft-auth/cloaks/%login%.png";
    const REGEX_USERNAME = "\w{1,16}$";
    const REGEX_UUID = "[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}";
    const GIVE_DEFAULT = false; // Выдавать ли этим скриптом default скины и плащи, если упомянутые не найдены в папках. SKIN_URL и CLOAK_URL должны содержать внешний путь к этому скрипту и ?login=%login% в конце
    const SKIN_DEFAULT = "iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgCAMAAACVQ462AAAAWlBMVEVHcEwsHg51Ri9qQC+HVTgjIyNOLyK7inGrfWaWb1udZkj///9SPYmAUjaWX0FWScwoKCgAzMwAXl4AqKgAaGgwKHImIVtGOqU6MYkAf38AmpoAr68/Pz9ra2t3xPtNAAAAAXRSTlMAQObYZgAAAZJJREFUeNrUzLUBwDAUA9EPMsmw/7jhNljl9Xdy0J3t5CndmcOBT4Mw8/8P4pfB6sNg9yA892wQvwzSIr8f5JRzSeS7AaiptpxazUq8GPQB5uSe2DH644GTsDFsNrqB9CcDgOCAmffegWWwAExnBrljqowsFBuGYShY5oakgOXs/39zF6voDG9r+wLvTCVUcL+uV4m6uXG/L3Ut691697tgnZgJavinQHOB7DD8awmaLWEmaNuu7YGf6XcIITRm19P1ahbARCRGEc8x/UZ4CroXAQTVIGL0YySrREBADFGicS8XtG8CTS+IGU2F6EgSE34VNKoNz8348mzoXGDxpxkQBpg2bWobjgZSm+uiKDYH2BAO8C4YBmbgAjpq5jUl4yGJC46HQ7HJBfkeTAImIEmgmtpINi44JsHx+CKA/BTuArISXeBTR4AI5gK4C2JqRfPs0HNBkQnG8S4Yxw8IGoIZfXEBOW1D4YJDAdNSXgRevP+ylK6fGBCwsWywmA19EtBkJr8K2t4N5pnAVwH0jptsBp+2gUFj4tL5ywAAAABJRU5ErkJggg==";
    const CLOAK_DEFAULT = "iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgAQMAAACYU+zHAAAAA1BMVEVHcEyC+tLSAAAAAXRSTlMAQObYZgAAAAxJREFUeAFjGAV4AQABIAABL3HDQQAAAABJRU5ErkJggg==";

    public static function getSkinURL($login)
    {
        return str_replace('%login%', $login, self::SKIN_URL) . (contains(self::SKIN_URL, $_SERVER['PHP_SELF'] . '?login=%login%') ? '&type=skin' : '');
    }
    public static function getCloakURL($login)
    {
        return str_replace('%login%', $login, self::CLOAK_URL) . (contains(self::CLOAK_URL, $_SERVER['PHP_SELF'] . '?login=%login%') ? '&type=cloak' : '');
    }
    public static function getSkin($login)
    {
        $path = Check::ci_find_file(self::SKIN_PATH . $login . '.png');
        return $path ? file_get_contents($path) : (self::GIVE_DEFAULT && contains(self::SKIN_URL, $_SERVER['PHP_SELF']) ? base64_decode(self::SKIN_DEFAULT) : null);
    }
    public static function getCloak($login)
    {
        $path = Check::ci_find_file(self::CLOAK_PATH . $login . '.png');
        return $path ? file_get_contents($path) : (self::GIVE_DEFAULT && contains(self::CLOAK_URL, $_SERVER['PHP_SELF']) ? base64_decode(self::CLOAK_DEFAULT) : null);
    }
}
class Check
{
    public static function skin($login)
    {
        $msg = [];
        $data = Constants::getSkin($login);
        if (isset($data)) {
            $msg = array(
                'url' => Constants::getSkinURL($login),
                'digest' => base64_encode(md5($data))
            );
            if (self::slim($data)) $msg['metadata'] = array('model' => 'slim');
        }
        return $msg;
    }
    private static function slim($data)
    {
        $image = imagecreatefromstring($data);
        $fraction = imagesx($image) / 8;
        $x = $fraction * 6.75;
        $y = $fraction * 2.5;
        $rgba = imagecolorsforindex($image, imagecolorat($image, $x, $y));
        if ($rgba["alpha"] === 127)
            return true;
        else return false;
    }
    public static function cloak($login)
    {
        $msg = [];
        $data = Constants::getCloak($login);
        if (isset($data)) {
            $msg = array(
                'url' => Constants::getCloakURL($login),
                'digest' => base64_encode(md5($data))
            );
        }
        return $msg;
    }
    public static function ci_find_file($filename)
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
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    regex_valid($login) ?: response();
    if (!empty($type)) getTexture($login, $type);
    $msg = [];
    $skin = Check::skin($login);
    if (!empty($skin)) $msg['skin'] = $skin;
    $cloak = Check::cloak($login);
    if (!empty($cloak)) $msg['cloak'] = $cloak;
    response($msg);
}
function getTexture($login, $type)
{
    header("Content-type: image/png");
    switch ($type) {
        case 'cloak':
            die(Constants::getCloak($login));
        default:
            die(Constants::getSkin($login));
    }
}
function response($msg = null)
{
    header("Content-Type: application/json; charset=UTF-8");
    die(json_encode((object) $msg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
function regex_valid($var)
{
    if (!is_null($var) && (preg_match("/^" . Constants::REGEX_USERNAME . "/", $var, $varR) ||
        preg_match("/" . Constants::REGEX_UUID . "/", $var, $varR)))
        return true;
}
function contains($haystack, $needle)
{
    return strpos($haystack, $needle) !== false;
}
