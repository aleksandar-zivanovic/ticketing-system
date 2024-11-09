<?php
session_start();

require_once '../helpers/functions.php';

echo "<h1>INDEX</h1>";

// handling login error message
handleSessionMessages('info_message', true);

echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

session_unset();
session_destroy();