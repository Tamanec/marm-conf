<?php

namespace mc\commands;


use mc\models\LocalConfStorage;
use mc\models\LocalConfStorageFactory;
use mc\models\RemoteConfStorageFactory;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushConfCommand extends Command {

    /**
     * @var LocalConfStorageFactory
     */
    private $localStorageFactory;

    /**
     * @var RemoteConfStorageFactory
     */
    private $remoteStorageFactory;

    /**
     * @var string
     */
    private $path;

    /**
     * PullConfCommand constructor.
     * @param LocalConfStorageFactory $localStorageFactory
     * @param RemoteConfStorageFactory $remoteStorageFactory
     * @param string $path
     */
    public function __construct(LocalConfStorageFactory $localStorageFactory, RemoteConfStorageFactory $remoteStorageFactory, string $path) {
        $this->localStorageFactory = $localStorageFactory;
        $this->remoteStorageFactory = $remoteStorageFactory;
        $this->path = $path;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName("mc:push-conf")
            ->setDescription("Получает конфигурацию из ФС и сохраняет в БД.")
            ->setHelp("Переносит конфигурацию из ФС в БД. Для удаления данных в БД необходимо указать указать аргумент delete=true.\nПример:\nphp index.php mc:pull-conf nadzor v1d1")
            ->addArgument("project", InputArgument::REQUIRED, "Project name (token)")
            ->addArgument("version", InputArgument::REQUIRED, "Configuration version")
            ->addArgument("delete", InputArgument::OPTIONAL, "Delete data from DB")
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = $input->getArgument("project");
        $version = $input->getArgument("version");
        $delete = filter_var($input->getArgument("delete"), FILTER_VALIDATE_BOOLEAN);

        if (!preg_match('/v([\d]+)d([\d]+)/', $version, $matches)) {
            throw new \Exception("Incorrect format of version: {$version}");
        }

        $confVersion = $matches[2];
        $remoteStorage = $this->remoteStorageFactory->create($project, $confVersion);
        if ($delete) {
            $remoteStorage->drop();
        }

        $localStorage = $this->localStorageFactory->create($project, $this->path);
        foreach ($localStorage->getCollections() as $collectionName) {
            $iterator = $localStorage->getCollectionData($collectionName);
            foreach ($iterator as $data) {
                $remoteStorage->save($data, $collectionName);
            }

            $remoteStorage->flush($collectionName);
        }
    }

}