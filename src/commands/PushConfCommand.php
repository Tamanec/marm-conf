<?php

namespace mc\commands;


use mc\services\PushConfService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushConfCommand extends Command {

    /**
     * @var PushConfService
     */
    private $pushConfService;

    /**
     * PullConfCommand constructor.
     * @param PushConfService $pushConfService
     */
    public function __construct(PushConfService $pushConfService) {
        $this->pushConfService = $pushConfService;
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

        $this->pushConfService->pushConf($project, $version, $delete);

        $output->writeln("Отправка конфигурации завершена");
    }

}