<?php

use ZPHP\ZPHP;
$rootPath = dirname(__DIR__);
require $rootPath.'vendor/autoload.php';
ZPHP::run($rootPath, true, "register");