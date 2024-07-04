<?php

use Microwin7\PHPUtils\Rules\Regex;

require_once(__DIR__ . '/../../vendor/autoload.php');

/** @var string $env_filename */
if (($env_lines = file(__DIR__ . '/../../' . (($env_filename = getenv('ENV_VENDOR')) === false ? '.env' : '.env.' . $env_filename), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) !== false) {
    $finalLines = array_filter($env_lines, function ($line) {
        return Regex::valid_with_pattern($line, '/^([A-Z0-9\_]+)=(.*?)$/');
    });
    foreach ($finalLines as $line) {
        preg_match('/^([A-Z0-9\_]+)=.*?$/', $line, $matches);
        if (getenv($matches[1]) === false) putenv($matches[0]);
    }
}
