<?php

use ZPHP\ZPHP;

$rootPath = dirname(__DIR__);
require 'vendor/autoload.php';
ZPHP::run($rootPath, true, "register");