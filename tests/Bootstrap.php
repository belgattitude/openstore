<?php

use ModulesTests\ServiceManagerGrabber;

error_reporting(E_ALL | E_STRICT);

$cwd = dirname(__FILE__);
chdir(dirname(__DIR__));

// Assume we use composer
$autoload_file = realpath($cwd . '/../vendor/autoload.php');
if (!$autoload_file) {
    throw new \Exception("Cannot determine path of autoload.php, must be used with composer");
}

$loader = require $autoload_file;
$loader->add("ModulesTests\\", $cwd);
$loader->add("Openstore\\", $cwd . '/../module/Openstore/src');
$loader->add("OpenstoreApi\\", $cwd . '/../module/OpenstoreApi/src');
$loader->add("Akilia\\", $cwd . '/../module/Akilia/src');
$loader->register();

ServiceManagerGrabber::setServiceConfig(require_once './config/application.config.php');

// Remove db adapter profiling messages
$smg = new ServiceManagerGrabber();
$adapter = $smg->getServiceManager()->get('Zend\Db\Adapter\Adapter');

if ($adapter instanceof \BjyProfiler\Db\Adapter\ProfilingAdapter) {
    $profiler = $adapter->getProfiler();
    if ($profiler instanceof \BjyProfiler\Db\Profiler\LoggingProfiler) {
        $adapter->setProfiler(new \BjyProfiler\Db\Profiler\Profiler($enabled = false));
        $adapter->getProfiler()->disable();
        $profiler->setPriority(\Zend\Log\Logger::DEBUG);
    }
}




ob_start();
