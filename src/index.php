<?php

namespace mc;

use Dotenv\Dotenv;
use mc\commands\PullConfCommand;
use mc\commands\PushConfCommand;
use mc\models\LocalConfStorageFactory;
use mc\models\RemoteConfStorageFactory;
use Symfony\Component\Console\Application;

require_once __DIR__ . "/vendor/autoload.php";

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$application = new Application();

$application->add(new PullConfCommand(
    new LocalConfStorageFactory(),
    new RemoteConfStorageFactory(),
    __DIR__ . DIRECTORY_SEPARATOR . "projects"
));
$application->add(new PushConfCommand(
    new LocalConfStorageFactory(),
    new RemoteConfStorageFactory(),
    __DIR__ . DIRECTORY_SEPARATOR . "projects"
));

$application->run();