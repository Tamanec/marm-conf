<?php

namespace mc\services;


use mc\models\ArchiveFactory;
use mc\models\LocalStorage;
use Symfony\Component\Filesystem\Filesystem;

class ArchiveService {

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var ArchiveFactory
     */
    private $archiveFactory;

    /**
     * @var LocalStorage
     */
    private $localStorage;

    /**
     * ArchiveService constructor.
     * @param Filesystem $fs
     * @param ArchiveFactory $archiveFactory
     */
    public function __construct(Filesystem $fs, ArchiveFactory $archiveFactory, LocalStorage $localStorage) {
        $this->fs = $fs;
        $this->archiveFactory = $archiveFactory;
        $this->localStorage = $localStorage;
    }

    /**
     * @param string $project
     * @param string $version
     * @return string
     */
    public function compressConf($project, $version) {
        $path = $this->localStorage->getPath();
        $fullArchiveName = $path . DIRECTORY_SEPARATOR . $this->getArchiveName($project, $version);
        $this->fs->remove($fullArchiveName);

        $tar = $this->archiveFactory->createArchive($fullArchiveName);
        if (!$tar->createModify(array($path . DIRECTORY_SEPARATOR . $project), '', $path)) {
            throw new \Exception($tar->error_object->getMessage());
        }

        return $fullArchiveName;
    }

    /**
     * @param string $project
     * @param string $version
     */
    public function uncompressConf($project, $version) {
        $path = $this->localStorage->getPath();
        $fullArchiveName = $path . DIRECTORY_SEPARATOR . $this->getArchiveName($project, $version);
        if (!file_exists($fullArchiveName)) {
            throw new \Exception("Архив не найден: {$fullArchiveName}");
        }

        $this->localStorage->remove($project);
        $tar = $this->archiveFactory->createArchive($fullArchiveName);
        if (!$tar->extract($path)) {
            throw new \Exception($tar->error_object->getMessage());
        }
    }

    private function getArchiveName($project, $version) {
        return "{$project}-{$version}.tar.gz";
    }

}