<?php

namespace mc\services;


use mc\models\LocalStorage;

class ArchiveService {

    /**
     * @var LocalStorage
     */
    private $localStorage;

    /**
     * ArchiveService constructor.
     * @param LocalStorage $localStorage
     */
    public function __construct(LocalStorage $localStorage) {
        $this->localStorage = $localStorage;
    }

    /**
     * @param string $project
     * @param string $version
     * @return string
     */
    public function compressConf($project, $version) {
        return $this->localStorage->compress($this->getArchiveName($project, $version), $project);
    }

    /**
     * @param string $project
     * @param string $version
     */
    public function uncompressConf($project, $version) {
        $this->localStorage->uncompress($this->getArchiveName($project, $version), $project);
    }

    private function getArchiveName($project, $version) {
        return "{$project}-{$version}.tar.gz";
    }

}