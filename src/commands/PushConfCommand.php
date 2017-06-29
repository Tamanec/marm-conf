<?php

namespace mc\commands;


use mc\models\LocalConfStorage;
use mc\models\RemoteConfStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushConfCommand extends Command {

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
            ->setName("mc:push-conf")
            ->setDescription("Получает конфигурацию из ФС и сохраняет в БД.")
            ->setHelp("Переносит конфигурацию из ФС в БД. Для удаления данных в БД необходимо указать указать аргумент delete=true.\nПример:\nphp index.php mc:pull-conf nadzor v1d1")
            ->addArgument("project", InputArgument::REQUIRED, "Project name (token)")
            ->addArgument("version", InputArgument::REQUIRED, "Configuration version")
            ->addArgument("delete", InputArgument::OPTIONAL, "Drop db before push")
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
        $this->remoteStorage
            ->setProject($project)
            ->setConfVersion($confVersion)
        ;
        if ($delete) {
            $this->remoteStorage->drop();
        }

        $this->localStorage->setProject($project);
        foreach ($this->localStorage->getCollections() as $collectionName) {
            $iterator = $this->localStorage->getCollectionData($collectionName);
            foreach ($iterator as $data) {
                $this->remoteStorage->save($data, $collectionName);
            }

            $this->remoteStorage->flush($collectionName);
        }
    }

}