<?php

namespace mc\services;


use mc\models\LocalConfStorage;
use mc\models\RemoteConfStorage;

class PushConfService {

    /**
     * @var LocalConfStorage
     */
    private $localStorage;

    /**
     * @var RemoteConfStorage
     */
    private $remoteStorage;

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
     * @param $project
     * @param $version
     * @param bool $delete
     * @return array Статистика отправленных шаблонов
     * @throws \Exception
     */
    public function pushConf($project, $version, $delete = false) {
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
        $stat = [];
        foreach ($this->localStorage->getCollections() as $collectionName) {
            $stat[$collectionName] = 0;
            $iterator = $this->localStorage->getCollectionData($collectionName);
            foreach ($iterator as $data) {
                $this->remoteStorage->save($data, $collectionName);
                $stat[$collectionName]++;
            }

            $this->remoteStorage->flush($collectionName);
        }

        return $stat;
    }

}