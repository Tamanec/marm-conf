<?php

namespace mc\commands;


use mc\models\LocalConfStorage;
use mc\models\RemoteConfStorage;
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
     * @var LocalConfStorage
     */
    private $localStorage;

    /**
     * @var RemoteConfStorage
     */
    private $remoteStorage;

    /**
     * PullConfCommand constructor.
     * @param LocalConfStorage $localStorage
     * @param RemoteConfStorage $remoteStorage
     */
    public function __construct(LocalConfStorage $localStorage, RemoteConfStorage $remoteStorage) {
        $this->localStorage = $localStorage;
        $this->remoteStorage = $remoteStorage;
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

        $this->localStorage->setProject($project);
        $this->localStorage->initProject();

        $confVersion = $matches[2];
        $this->remoteStorage
            ->setProject($project)
            ->setConfVersion($confVersion)
        ;
        foreach ($this->remoteStorage->getCollections() as $collectionName) {
            if (in_array($collectionName, $this->ignoreCollections)) {
                continue;
            }

            $this->localStorage->initCollection($collectionName);
            foreach ($this->remoteStorage->getCollectionData($collectionName) as $data) {
                $this->localStorage->save($data, $collectionName);
            }
        }
    }

}