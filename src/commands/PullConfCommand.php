<?php

namespace mc\commands;


use mc\services\ArchiveService;
use mc\services\PullConfService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PullConfCommand extends Command {

    /**
     * @var PullConfService
     */
    private $pullConfService;

    /**
     * @var ArchiveService
     */
    private $archiveService;

    /**
     * PullConfCommand constructor.
     * @param PullConfService $pullConfService
     */
    public function __construct(PullConfService $pullConfService, ArchiveService $archiveService) {
        $this->pullConfService = $pullConfService;
        $this->archiveService = $archiveService;
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

        $stat = $this->pullConfService->pullConf($project, $version);
        $fullPathToArchive = $this->archiveService->compressConf($project, $version);

        $output->writeln("Получение конфигурации завершено");
        $output->writeln(var_export($stat, true));
        $output->writeln($fullPathToArchive);
    }

}