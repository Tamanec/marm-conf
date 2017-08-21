<?php

namespace mc\services;


use mc\models\LocalConfStorage;
use mc\models\RemoteConfStorage;

class PullConfService {

    /**
     * @var LocalConfStorage
     */
    private $localStorage;

    /**
     * @var RemoteConfStorage
     */
    private $remoteStorage;

    private $ignoreCollections = [
        "system.indexes",
        "system.profile",
        "sysinfo",
        "files"
    ];

    /**
     * PushConfService constructor.
     * @param LocalConfStorage $localStorage
     * @param RemoteConfStorage $remoteStorage
     */
    public function __construct(LocalConfStorage $localStorage, RemoteConfStorage $remoteStorage) {
        $this->localStorage = $localStorage;
        $this->remoteStorage = $remoteStorage;
    }

    /**
     * @param string $project
     * @param string $version
     * @return array Статистика скачанных шаблонов
     * @throws \Exception
     */
    public function pullConf(string $project, string $version) : array {
        if (!preg_match('/v([\d]+)d([\d]+)/', $version, $matches)) {
            throw new \Exception("Incorrect format of version: {$version}");
        }

        $confVersion = $matches[2];
        $this->remoteStorage
            ->setProject($project)
            ->setConfVersion($confVersion)
        ;
        if (!$this->remoteStorage->dbExists()) {
            throw new \Exception("База данных не существует.");
        }

        $this->localStorage->setProject($project);
        $this->localStorage->initProject();

        $stat = [];
        foreach ($this->remoteStorage->getCollections() as $collectionName) {
            if (in_array($collectionName, $this->ignoreCollections)) {
                continue;
            }

            $this->localStorage->initCollection($collectionName);
            $stat[$collectionName] = 0;
            foreach ($this->remoteStorage->getCollectionData($collectionName) as $data) {
                $this->localStorage->save($data, $collectionName);
                $stat[$collectionName]++;
            }
        }

        return $stat;
    }

}