<?php

namespace mc\models;

class RemoteConfStorageFactory {

    public function create(string $project, int $confVersion): RemoteConfStorage {
        return new RemoteConfStorage($project, $confVersion);
    }

}