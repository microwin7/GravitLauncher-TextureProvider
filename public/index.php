<?php

use Microwin7\TextureProvider\InitRequest;

// ini_set('error_reporting', E_ALL); // FULL DEBUG
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require_once(__DIR__ . '/../vendor/autoload.php');
// require_once(__DIR__ . '/../../texture-provider/vendor/autoload.php'); // Для выноса за пределы ROOT_FOLDER

// Registration ExceptionHandler
new \Microwin7\PHPUtils\Exceptions\Handler\ExceptionHandler;

new InitRequest;
