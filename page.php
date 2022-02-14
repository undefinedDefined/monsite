<?php

define("BASE_PATH", "/monsite");

echo '<pre>';
$chemin = $_SERVER["REQUEST_URI"];
$test = substr($chemin, strlen(BASE_PATH)+1);
$test2 = explode("/", $chemin);
var_dump($_GET);
echo '</pre>';
