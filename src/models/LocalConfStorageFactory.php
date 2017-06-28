<?php

namespace mc\models;

use Symfony\Component\Filesystem\Filesystem;

class LocalConfStorageFactory {

    public function create(string $project, string $path): LocalConfStorage {
        return (new LocalConfStorage(new Filesystem()))
            ->setProject($project)
            ->setPath($path)
        ;
    }

}