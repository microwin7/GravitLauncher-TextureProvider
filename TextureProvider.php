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
    const CAPE_PATH = "./minecraft-auth/capes/"; // Сюда вписать путь до capes/
    const SKIN_URL = "https://example.com/minecraft-auth/skins/%login%.png";
    const CAPE_URL = "https://example.com/minecraft-auth/capes/%login%.png";
    const REGEX_USERNAME = "\w{1,16}$";
    const REGEX_UUIDv1 = "\b[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}\b";
    const REGEX_UUIDv4 = "[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{4}\-[a-f0-9]{12}";
    const GIVE_DEFAULT = false; // Выдавать ли этим скриптом default скины и плащи, если упомянутые не найдены в папках. SKIN_URL и CAPE_URL должны содержать внешний путь к этому скрипту и ?login=%login% в конце
    const SKIN_DEFAULT = "iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgCAMAAACVQ462AAAAWlBMVEVHcEwsHg51Ri9qQC+HVTgjIyNOLyK7inGrfWaWb1udZkj///9SPYmAUjaWX0FWScwoKCgAzMwAXl4AqKgAaGgwKHImIVtGOqU6MYkAf38AmpoAr68/Pz9ra2t3xPtNAAAAAXRSTlMAQObYZgAAAZJJREFUeNrUzLUBwDAUA9EPMsmw/7jhNljl9Xdy0J3t5CndmcOBT4Mw8/8P4pfB6sNg9yA892wQvwzSIr8f5JRzSeS7AaiptpxazUq8GPQB5uSe2DH644GTsDFsNrqB9CcDgOCAmffegWWwAExnBrljqowsFBuGYShY5oakgOXs/39zF6voDG9r+wLvTCVUcL+uV4m6uXG/L3Ut691697tgnZgJavinQHOB7DD8awmaLWEmaNuu7YGf6XcIITRm19P1ahbARCRGEc8x/UZ4CroXAQTVIGL0YySrREBADFGicS8XtG8CTS+IGU2F6EgSE34VNKoNz8348mzoXGDxpxkQBpg2bWobjgZSm+uiKDYH2BAO8C4YBmbgAjpq5jUl4yGJC46HQ7HJBfkeTAImIEmgmtpINi44JsHx+CKA/BTuArISXeBTR4AI5gK4C2JqRfPs0HNBkQnG8S4Yxw8IGoIZfXEBOW1D4YJDAdNSXgRevP+ylK6fGBCwsWywmA19EtBkJr8K2t4N5pnAVwH0jptsBp+2gUFj4tL5ywAAAABJRU5ErkJggg==";
    const CAPE_DEFAULT = "iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgAQMAAACYU+zHAAAAA1BMVEVHcEyC+tLSAAAAAXRSTlMAQObYZgAAAAxJREFUeAFjGAV4AQABIAABL3HDQQAAAABJRU5ErkJggg==";

    public static function getSkinURL($login)
    {
        return str_replace('%login%', $login, self::SKIN_URL) . (contains(self::SKIN_URL, $_SERVER['PHP_SELF'] . '?login=%login%') ? '&type=skin' : '');
    }
    public static function getCapeURL($login)
    {
        return str_replace('%login%', $login, self::CAPE_URL) . (contains(self::CAPE_URL, $_SERVER['PHP_SELF'] . '?login=%login%') ? '&type=cape' : '');
    }
    public static function getSkin($login)
    {
        $path = Check::ci_find_file(self::SKIN_PATH . $login . '.png');
        return $path ? file_get_contents($path) : (self::GIVE_DEFAULT && contains(self::SKIN_URL, $_SERVER['PHP_SELF']) ? base64_decode(self::SKIN_DEFAULT) : null);
    }
    public static function getCape($login)
    {
        $path = Check::ci_find_file(self::CAPE_PATH . $login . '.png');
        return $path ? file_get_contents($path) : (self::GIVE_DEFAULT && contains(self::CAPE_URL, $_SERVER['PHP_SELF']) ? base64_decode(self::CAPE_DEFAULT) : null);
    }
    public static function getDataUrl($url)
    {
        $data = file_get_contents($url, false, stream_context_create(['http' => ['ignore_errors' => true]]));
        $headers = self::parseHeaders($http_response_header);
        ($headers['reponse_code'] == 200) ?: response();
        return $data;
    }
    private static function parseHeaders($headers)
    {
        $head = array();
        foreach ($headers as $key => $value) {
            $t = explode(':', $value, 2);
            if (isset($t[1]))
                $head[trim($t[0])] = trim($t[1]);
            else {
                $head[] = $value;
                if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $value, $out))
                    $head['reponse_code'] = intval($out[1]);
            }
        }
        return $head;
    }
}
class Mojang
{
    public static $uuid = null;
    public static $textures = null;
    public static $mojangSkinUrl = null;
    public static $mojangSkinSlim = null;
    public static $mojangSkin = null;
    public static $mojangCapeUrl = null;
    public static $mojangCape = null;

    function __construct()
    {
        self::getMojangUUID();
        self::getMojangTextures();
        self::mojangSkinUrl();
        self::mojangSkin();
        self::mojangCapeUrl();
        self::mojangCape();
    }

    public static function getMojangUUID()
    {
        if (self::$uuid == null) {
            $login = $_GET['login'];
            $data = Constants::getDataUrl('https://api.mojang.com/users/profiles/minecraft/' . $login);
            return self::$uuid = json_decode($data, true)['id'];
        } else return self::$uuid;
    }
    public static function getMojangTextures()
    {
        if (self::$textures == null) {
            $data = Constants::getDataUrl('https://sessionserver.mojang.com/session/minecraft/profile/' . self::$uuid);
            return self::$textures = json_decode(base64_decode(json_decode($data, true)['properties'][0]['value']), true)['textures'];
        } else return self::$textures;
    }
    public static function mojangSkinUrl()
    {
        if (self::$mojangSkinUrl == null) {
            return self::$mojangSkinUrl = self::getMojangTextures()['SKIN']['url'];
        } else return self::$mojangSkinUrl;
    }
    public static function mojangSkin()
    {
        if (self::$mojangSkin == null) {
            return self::$mojangSkin = Constants::getDataUrl(self::$mojangSkinUrl);
        } else return self::$mojangSkin;
    }
    public static function mojangSkinSlim()
    {
        if (self::$mojangSkinSlim == null) {
            return self::$mojangSkinSlim = isset(self::getMojangTextures()['SKIN']['metadata']['model']) ? true : false;
        } else return self::$mojangSkinSlim;
    }
    public static function mojangCapeUrl()
    {
        if (self::$mojangCapeUrl == null) {
            return self::$mojangCapeUrl = isset(self::getMojangTextures()['CAPE']['url']) ? self::getMojangTextures()['CAPE']['url'] : false;
        } else return self::$mojangCapeUrl;
    }
    public static function mojangCape()
    {
        if (self::$mojangCapeUrl == null) return false;
        if (self::$mojangCape == null) {
            return self::$mojangCape = Constants::getDataUrl(self::$mojangCapeUrl);
        } else return self::$mojangCape;
    }
}
class Check
{
    public static function skin($login, $method = 'normal', $skin = null, $skinUrl = null, $skinSlim = null)
    {
        $msg = [];
        if ($method == 'normal') {
            $data =  Constants::getSkin($login);
            if (isset($data)) {
                $msg = array(
                    'url' =>  Constants::getSkinURL($login),
                    'digest' => self::digest($data)
                );
                if (self::slim($data)) $msg['metadata'] = array('model' => 'slim');
            }
        } else {
            if (!empty($skin)) {
                $msg = array(
                    'url' =>  $skinUrl,
                    'digest' => self::digest($skin)
                );
                if ($skinSlim) $msg['metadata'] = array('model' => 'slim');
            }
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
    public static function cape($login, $method = 'normal', $cape = null, $capeUrl = null)
    {
        $msg = [];
        if ($method == 'normal') {
            $data = isset($data) ? $data :  Constants::getCape($login);
            if (isset($data)) {
                $msg = array(
                    'url' => Constants::getCapeURL($login),
                    'digest' => self::digest($data)
                );
            }
        } else {
            if (!empty($cape)) {
                $msg = array(
                    'url' => $capeUrl,
                    'digest' => self::digest($cape)
                );
            }
        }
        return $msg;
    }
    private static function digest($string)
    {
        return strtr(base64_encode(md5($string, true)), '+/', '-_');
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
    if (extension_loaded('gd')) die(header("HTTP/1.0 403 Please enable or install the GD extension in your php.ini"));
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    $login = isset($_GET['login']) ? $_GET['login'] : null;
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    $method = isset($_GET['method']) ? $_GET['method'] : null; // normal, mojang, hybrid (последнее ещё будет)
    $method = getMethod($method);
    ($method == 'normal' ? regex_valid_username($login) || regex_valid_uuid($login) : regex_valid_username($login)) ?: response();
    if ($method == 'mojang') {
        $mojang = new Mojang();
        $msg = [];
        $skin = Check::skin($login, $method, $mojang::mojangSkin(), $mojang::mojangSkinUrl(), $mojang::mojangSkinSlim());
        if (!empty($skin)) $msg['SKIN'] = $skin;
        $cape = Check::cape($login, $method, $mojang::mojangCape(), $mojang::mojangCapeUrl());
        if (!empty($cape)) $msg['CAPE'] = $cape;
        response($msg);
    } else if ($method == 'hybrid') {
		if (!empty($type)) getTexture($login, $type);
		$skin = Check::skin($login);
		if(empty($skin))
		{
			$mojang = new Mojang();
			$msg = [];
			$skin = Check::skin($login, $method, $mojang::mojangSkin(), $mojang::mojangSkinUrl(), $mojang::mojangSkinSlim());
			if (!empty($skin)) $msg['SKIN'] = $skin;
			$cape = Check::cape($login, $method, $mojang::mojangCape(), $mojang::mojangCapeUrl());
			if (!empty($cape)) $msg['CAPE'] = $cape;
		}
		else
		{
			$msg['SKIN'] = $skin;
			$cape = Check::cape($login);
			if (!empty($cape)) $msg['CAPE'] = $cape;
		}
        response($msg);
    } else {
        if (!empty($type)) getTexture($login, $type);
        $msg = [];
        $skin = Check::skin($login);
        if (!empty($skin)) $msg['SKIN'] = $skin;
        $cape = Check::cape($login);
        if (!empty($cape)) $msg['CAPE'] = $cape;
        response($msg);
    }
}
function getTexture($login, $type)
{
    header("Content-type: image/png");
    switch ($type) {
        case 'cape':
            die(Constants::getCape($login));
        default:
            die(Constants::getSkin($login));
    }
}
function getMethod($method)
{
    switch ($method) {
        case 'mojang' || 'hybrid':
            return $method;
        default:
            return 'normal';
    }
}
function response($msg = null)
{
    header("Content-Type: application/json; charset=UTF-8");
    die(json_encode((object) $msg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
function regex_valid_username($var)
{
    if (!is_null($var) && (preg_match("/^" . Constants::REGEX_USERNAME . "/", $var, $varR)))
        return true;
}
function regex_valid_uuid($var)
{
    if (!is_null($var) && (preg_match("/" . Constants::REGEX_UUIDv1 . "/", $var, $varR) ||
        preg_match("/" . Constants::REGEX_UUIDv4 . "/", $var, $varR)))
        return true;
}
function contains($haystack, $needle)
{
    return strpos($haystack, $needle) !== false;
}
function exists(...$var)
{
    $i = true;
    foreach ($var as $v) {
        $i = (!empty($v) && isset($v) && $i) ? true : false;
    }
    return $i;
}
