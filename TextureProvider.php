<?php
#
# Скрипт выдачи скинов и плащей. GravitLauncher 5.2.0+
#
# https://github.com/microwin7/GravitLauncher-TextureProvider
#
mb_internal_encoding("UTF-8");
start();
class Constants
{
    const SKIN_PATH = "./minecraft-auth/skins/"; // Сюда вписать путь до skins/
    const CAPE_PATH = "./minecraft-auth/capes/"; // Сюда вписать путь до capes/
    const SKIN_URL = "https://example.com/minecraft-auth/skins/%login%.png";
    const CAPE_URL = "https://example.com/minecraft-auth/capes/%login%.png";
    const REGEX_USERNAME = "\w{1,16}$";
    const REGEX_UUID_NO_DASH = "[0-9a-f]{32}";
    const GIVE_DEFAULT = false; // Выдавать ли этим скриптом default скины и плащи, если упомянутые не найдены в папках. SKIN_URL и CAPE_URL должны содержать внешний путь к этому скрипту и ?login=%login% в конце
    const SKIN_DEFAULT = "iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgCAMAAACVQ462AAAAWlBMVEVHcEwsHg51Ri9qQC+HVTgjIyNOLyK7inGrfWaWb1udZkj///9SPYmAUjaWX0FWScwoKCgAzMwAXl4AqKgAaGgwKHImIVtGOqU6MYkAf38AmpoAr68/Pz9ra2t3xPtNAAAAAXRSTlMAQObYZgAAAZJJREFUeNrUzLUBwDAUA9EPMsmw/7jhNljl9Xdy0J3t5CndmcOBT4Mw8/8P4pfB6sNg9yA892wQvwzSIr8f5JRzSeS7AaiptpxazUq8GPQB5uSe2DH644GTsDFsNrqB9CcDgOCAmffegWWwAExnBrljqowsFBuGYShY5oakgOXs/39zF6voDG9r+wLvTCVUcL+uV4m6uXG/L3Ut691697tgnZgJavinQHOB7DD8awmaLWEmaNuu7YGf6XcIITRm19P1ahbARCRGEc8x/UZ4CroXAQTVIGL0YySrREBADFGicS8XtG8CTS+IGU2F6EgSE34VNKoNz8348mzoXGDxpxkQBpg2bWobjgZSm+uiKDYH2BAO8C4YBmbgAjpq5jUl4yGJC46HQ7HJBfkeTAImIEmgmtpINi44JsHx+CKA/BTuArISXeBTR4AI5gK4C2JqRfPs0HNBkQnG8S4Yxw8IGoIZfXEBOW1D4YJDAdNSXgRevP+ylK6fGBCwsWywmA19EtBkJr8K2t4N5pnAVwH0jptsBp+2gUFj4tL5ywAAAABJRU5ErkJggg==";
    const CAPE_DEFAULT = "iVBORw0KGgoAAAANSUhEUgAAAEAAAAAgAQMAAACYU+zHAAAAA1BMVEVHcEyC+tLSAAAAAXRSTlMAQObYZgAAAAxJREFUeAFjGAV4AQABIAABL3HDQQAAAABJRU5ErkJggg==";

    public static function getSkinURL($login)
    {
        return str_replace('%login%', $login, self::SKIN_URL) . (Check::contains(self::SKIN_URL, $_SERVER['PHP_SELF'] . '?login=%login%') ? '&type=skin' : '');
    }
    public static function getCapeURL($login)
    {
        return str_replace('%login%', $login, self::CAPE_URL) . (Check::contains(self::CAPE_URL, $_SERVER['PHP_SELF'] . '?login=%login%') ? '&type=cape' : '');
    }
    public static function getSkin($login)
    {
        $path = Check::ci_find_file(self::SKIN_PATH . $login . '.png');
        return $path ? [
            file_get_contents($path),
            mb_substr(
                mb_stristr($path, $login . '.png', false, mb_internal_encoding()),
                0,
                mb_strlen($login, mb_internal_encoding()),
                mb_internal_encoding()
            )
        ] : [(self::GIVE_DEFAULT && Check::contains(self::SKIN_URL, $_SERVER['PHP_SELF']) ? base64_decode(self::SKIN_DEFAULT) : null), $login];
    }
    public static function getCape($login)
    {
        $path = Check::ci_find_file(self::CAPE_PATH . $login . '.png');
        return $path ? [
            file_get_contents($path),
            mb_substr(
                mb_stristr($path, $login . '.png', false, mb_internal_encoding()),
                0,
                mb_strlen($login, mb_internal_encoding()),
                mb_internal_encoding()
            )
        ] : (self::GIVE_DEFAULT && Check::contains(self::CAPE_URL, $_SERVER['PHP_SELF']) ? base64_decode(self::CAPE_DEFAULT) : null);
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
        $head = [];
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
    private $login;
    private $uuid;
    private $textures;
    private $mojang_skin_url;
    private $mojang_skin_slim;
    private $mojang_skin;
    private $mojang_cape_url;
    private $mojang_cape;

    function __construct($login)
    {
        $this->login = $login;
        $this->uuid = Check::regex_valid_uuid_no_dash($login) ? str_replace('-', '', $login) : null;
        self::getMojangUUID();
        self::getMojangTextures();
        self::mojangSkinUrl();
        self::mojangSkin();
        self::mojangCapeUrl();
        self::mojangCape();
    }

    public function getMojangUUID()
    {
        if ($this->uuid == null) {
            $data = Constants::getDataUrl('https://api.mojang.com/users/profiles/minecraft/' . $this->login);
            return $this->uuid = json_decode($data, true)['id'];
        } else return $this->uuid;
    }
    public function getMojangTextures()
    {
        if ($this->textures == null) {
            $data = Constants::getDataUrl('https://sessionserver.mojang.com/session/minecraft/profile/' . $this->uuid);
            return $this->textures = json_decode(base64_decode(json_decode($data, true)['properties'][0]['value']), true)['textures'];
        } else return $this->textures;
    }
    public function mojangSkinUrl()
    {
        if ($this->mojang_skin_url == null) {
            return $this->mojang_skin_url = $this->textures['SKIN']['url'];
        } else return $this->mojang_skin_url;
    }
    public function mojangSkin()
    {
        if ($this->mojang_skin == null) {
            return $this->mojang_skin = Constants::getDataUrl($this->mojang_skin_url);
        } else return $this->mojang_skin;
    }
    public function mojangSkinSlim()
    {
        if ($this->mojang_skin_slim == null) {
            return $this->mojang_skin_slim = isset($this->textures['SKIN']['metadata']['model']) ? true : false;
        } else return $this->mojang_skin_slim;
    }
    public function mojangCapeUrl()
    {
        if ($this->mojang_cape_url == null) {
            return $this->mojang_cape_url = isset($this->textures['CAPE']['url']) ? $this->textures['CAPE']['url'] : false;
        } else return $this->mojang_cape_url;
    }
    public function mojangCape()
    {
        if ($this->mojang_cape_url == null) return false;
        if ($this->mojang_cape == null) {
            return $this->mojang_cape = Constants::getDataUrl($this->mojang_cape_url);
        } else return $this->mojang_cape;
    }
}
class Check
{
    public static function skin($login, $method = 'normal', $skin = null, $skinUrl = null, $skinSlim = null)
    {
        $msg = [];
        if ($method == 'normal') {
            [$data, $login] = Constants::getSkin($login);
            if (isset($data)) {
                $msg = [
                    'url' => Constants::getSkinURL($login),
                    'digest' => self::digest($data)
                ];
                if (self::slim($data)) $msg['metadata'] = ['model' => 'slim'];
            }
        } else {
            if (!empty($skin)) {
                $msg = [
                    'url' => $skinUrl,
                    'digest' => self::digest($skin)
                ];
                if ($skinSlim) $msg['metadata'] = ['model' => 'slim'];
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
            [$data, $login] = Constants::getCape($login);
            if (isset($data)) {
                $msg = [
                    'url' => Constants::getCapeURL($login),
                    'digest' => self::digest($data)
                ];
            }
        } else {
            if (!empty($cape)) {
                $msg = [
                    'url' => $capeUrl,
                    'digest' => self::digest($cape)
                ];
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
    public static function regex_valid_username($var)
    {
        if (!is_null($var) && (preg_match("/^" . Constants::REGEX_USERNAME . "/", $var, $varR)))
            return true;
    }
    public static function regex_valid_uuid_no_dash($var)
    {
        return (!is_null($var) && preg_match("/" . Constants::REGEX_UUID_NO_DASH . "/", str_replace('-', '', $var), $varR));
    }
    public static function contains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }
}
class Result
{
    protected $msg = [];

    function __get($key)
    {
        return $this->msg[$key];
    }
    function __set($key, $value)
    {
        if (!empty($value) && !isset($this->msg[$key])) $this->msg[$key] = $value;
    }
    public function getMsg()
    {
        return $this->msg;
    }
}
function start()
{
    if (!extension_loaded('gd')) die(header("HTTP/1.0 403 Please enable or install the GD extension in your php.ini"));
    // ini_set('error_reporting', E_ALL); // FULL DEBUG
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    $login = isset($_GET['login']) ? $_GET['login'] : null;
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    $method = isset($_GET['method']) ? $_GET['method'] : 'normal'; // normal, mojang, hybrid (Default: normal)
    Check::regex_valid_username($login) || Check::regex_valid_uuid_no_dash($login) ?: response();
    if (!empty($type)) getTexture($login, $type);
    $result = new Result;
    switch ($method) {
        case 'normal':
        case 'hybrid': {
                $result->SKIN = Check::skin($login);
                $result->CAPE = Check::cape($login);
                if ($method == 'normal') continue;
            }
        case 'mojang': {
                $mojang = new Mojang($login);
                $result->SKIN = Check::skin($login, $method, $mojang->mojangSkin(), $mojang->mojangSkinUrl(), $mojang->mojangSkinSlim());
                $result->CAPE = Check::cape($login, $method, $mojang->mojangCape(), $mojang->mojangCapeUrl());
                continue;
            }
        default:
            response();
    }
    response($result->getMsg());
}
function getTexture($login, $type)
{
    header("Content-type: image/png");
    switch ($type) {
        case 'cape':
            die(Constants::getCape($login)[0]);
        default:
            die(Constants::getSkin($login)[0]);
    }
}
function response($result = null)
{
    header("Content-Type: application/json; charset=UTF-8");
    die(json_encode((object) $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
