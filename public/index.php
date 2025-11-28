<?php
session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
require_once ROOT . 'helpers' . DS . 'functions.php';
require_once ROOT . 'helpers' . DS . 'view_helpers.php';
require_once ROOT . 'middleware' . DS . 'AuthMiddleware.php';

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$authMiddleware = new AuthMiddleware();
$authMiddleware->handle($url);

require_once ROOT . 'classes' . DS . 'Router.php';
$router = new Router($url);
$router->dispatch();
