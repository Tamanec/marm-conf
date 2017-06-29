<?php

namespace mc;

use Dotenv\Dotenv;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once __DIR__ . "/vendor/autoload.php";

define('ROOT_DIR', __DIR__);

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$container = new ContainerBuilder();
(new YamlFileLoader($container, new FileLocator(ROOT_DIR)))->load('services.yaml');
$container->compile(true);

$application = new Application();

$application->add($container->get('command.conf.pull'));
$application->add($container->get('command.conf.push'));

$application->run();