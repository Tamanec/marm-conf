<?php

namespace mc\models;


interface LocalStorage {


    /**
     * @return string Путь до папки с проектами
     */
    public function getPath();

    /**
     * Удаляет проект
     * @param string $project
     * @throws \Exception
     */
    public function remove($project);

}