<?php

namespace mc;

use Dotenv\Dotenv;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\LockHandler;

define('ROOT_DIR', realpath(__DIR__ . '/..'));

require_once ROOT_DIR . "/src/vendor/autoload.php";

$dotenv = new Dotenv(ROOT_DIR . '/config');
$dotenv->load();

$container = new ContainerBuilder();
(new YamlFileLoader($container, new FileLocator(ROOT_DIR . '/config')))->load('services.yaml');
$container->compile(true);

$application = new Application();

$dispatcher = new EventDispatcher();
$dispatcher->addListener(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) use ($container) {
    $lockHandler = new LockHandler('conf');
    if (!$lockHandler->lock()) {
        $event->getOutput()->writeln("Команда не может быть выполнена из-за блокировки.");
        $event->disableCommand();
    }

    $container->set('conf.locker', $lockHandler);
});
$application->setDispatcher($dispatcher);


$application->add($container->get('command.conf.pull'));
$application->add($container->get('command.conf.push'));

$application->run();