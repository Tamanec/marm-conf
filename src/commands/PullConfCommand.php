<?php

namespace mc\commands;


use mc\models\LocalConfStorageFactory;
use mc\models\RemoteConfStorageFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PullConfCommand extends Command {

    private $ignoreCollections = [
        "system.indexes",
        "system.profile",
        "sysinfo",
        "files"
    ];

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
            ->setName("mc:pull-conf")
            ->setDescription("Получает конфигурацию из БД и сохраняет в ФС.")
            ->setHelp("Переносит конфигурацию из БД в ФС. Данные на ФС предварительно удаляются.\nПример:\nphp index.php mc:pull-conf nadzor v1d1")
            ->addArgument("project", InputArgument::REQUIRED, "Project name (token)")
            ->addArgument("version", InputArgument::REQUIRED, "Configuration version")
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

        if (!preg_match('/v([\d]+)d([\d]+)/', $version, $matches)) {
            throw new \Exception("Incorrect format of version: {$version}");
        }

        $localStorage = $this->localStorageFactory->create($project, $this->path);
        $localStorage->initProject();

        $confVersion = $matches[2];
        $remoteStorage = $this->remoteStorageFactory->create($project, $confVersion);
        foreach ($remoteStorage->getCollections() as $collectionName) {
            if (in_array($collectionName, $this->ignoreCollections)) {
                continue;
            }

            $localStorage->initCollection($collectionName);
            foreach ($remoteStorage->getCollectionData($collectionName) as $data) {
                $localStorage->save($data, $collectionName);
            }
        }
    }

}