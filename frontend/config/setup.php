<?php
require_once("$_SERVER[DOCUMENT_ROOT]/../config/config.php");

// defined in nginx
define('APPENV', $_SERVER['APPENV'] ?? 'dev');

if (APPENV === 'dev') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    header("Cache-Control: no-cache");
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

require_once($_SERVER['APPDIR'] . '/vendor/autoload.php');
require_once($_SERVER['APPDIR'] . "/tools/tools.php");

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader($_SERVER['APPDIR'] . '/templates');
$twig = new Environment($loader, ['cache' => false]);
$twig->addGlobal('api_host', $API_HOST);
$twig->addGlobal('current_host', $CURRENT_HOST);
$twig->addGlobal('old_host', $OLD_HOST);
$twig->addGlobal('new_host', $NEW_HOST);