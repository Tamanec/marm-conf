<?php

use Dotenv\Dotenv;
use mc\services\ArchiveService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

require_once __DIR__ . "/vendor/autoload.php";

define('ROOT_DIR', __DIR__);

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$container = new ContainerBuilder();
(new YamlFileLoader($container, new FileLocator(ROOT_DIR)))->load('services.yaml');
$container->compile(true);

$app = new Silex\Application();

$app
    ->get('/pull/{project}/{version}', function($project, $version) use ($app, $container) {
        /** @var \mc\services\PullConfService $pullService */
        $pullService = $container->get('service.conf.pull');
        $pullService->pullConf($project, $version);

        /** @var ArchiveService $archiveService */
        $archiveService = $container->get('service.conf.archive');
        $fullPathToArchive = $archiveService->compressConf($project, $version);

        return $app
            ->sendFile($fullPathToArchive)
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($fullPathToArchive));
    })
    ->before(function () use ($app, $container) {
        $lockHandler = new LockHandler('conf');
        if (!$lockHandler->lock()) {
            $app->abort(500, "Команда не может быть выполнена из-за блокировки.");
        }

        $container->set('conf.locker', $lockHandler);
    });

$app
    ->post('/push/{project}/{version}/{delete}', function($project, $version, $delete, Request $request) use ($app, $container) {
        /** @var \mc\models\LocalConfStorage $localStorage */
        $localStorage = $container->get('conf.storage.local');

        /** @var UploadedFile $archive */
        $archive = $request->files->get('conf');
        $archive->move($localStorage->getPath(), $archive->getClientOriginalName());

        /** @var ArchiveService $archiveService */
        $archiveService = $container->get('service.conf.archive');
        $archiveService->uncompressConf($project, $version);

        /** @var \mc\services\PushConfService $pushService */
        $pushService = $container->get('service.conf.push');
        $stat = $pushService->pushConf($project, $version, $delete);
        return $app->json($stat);
    })
    ->value('delete', false)
    ->convert('delete', function ($delete) {
        return filter_var($delete, FILTER_VALIDATE_BOOLEAN);
    })
    ->before(function () use ($app, $container) {
        $lockHandler = new LockHandler('conf');
        if (!$lockHandler->lock()) {
            $app->abort(500, "Команда не может быть выполнена из-за блокировки.");
        }

        $container->set('conf.locker', $lockHandler);
    });

$app->error(function (\Exception $e, Request $request, $code) use ($app, $container) {
    $container
        ->get('logger')
        ->error(
            $e->getMessage(),
            [
                $request->getRequestUri(),
                $code,
                $e->getFile() . ":" . $e->getLine()
            ]
        );

    return $app->json([
        'error' => $e->getMessage()
    ], $code);
});

$app->before(function (Request $request) use ($app, $container) {
    if ($app['debug']) {
        $container->get('logger')->info($request->getRequestUri());
    }
});

$app['debug'] = getenv('DEBUG');
$app->run();