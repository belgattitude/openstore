<?php

use ModulesTests\ServiceManagerGrabber;
 
error_reporting(E_ALL | E_STRICT);
 
$cwd = __DIR__;
chdir(dirname(__DIR__));


// Assume we use composer
$loader = require_once  './vendor/autoload.php';
$loader->add("ModulesTests\\", $cwd);
$loader->add("Openstore\\", $cwd . '/../module/Openstore/src');
$loader->add("OpenstoreApi\\", $cwd . '/../module/OpenstoreApi/src');
$loader->add("Akilia\\", $cwd . '/../module/Akilia/src');
$loader->register();
 
ServiceManagerGrabber::setServiceConfig(require_once './config/application.config.php');
ob_start();