<?php

use DI\ContainerBuilder;

// include PHP-DI
require __DIR__ . '/../vendor/autoload.php';
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAnnotations(true);
$containerBuilder->addDefinitions(__DIR__ . '/config.php');
$containerBuilder->addDefinitions(__DIR__ . '/config.prod.php');

//$containerBuilder->setDefinitionCache(new Doctrine\Common\Cache\ApcuCache());
// todo: activate arrayCache while development
$containerBuilder->setDefinitionCache(new Doctrine\Common\Cache\ArrayCache());
$container = $containerBuilder->build();

return $container;
