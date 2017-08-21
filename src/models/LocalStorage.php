<?php

namespace mc\models;


interface LocalStorage {

    /**
     * @param string $archiveName Название файла архива
     * @param string $project
     * @return string Полный путь до архива
     */
    public function compress($archiveName, $project);

    /**
     * @param string $archiveName Название файла архива
     * @param string $project
     */
    public function uncompress($archiveName, $project);

}