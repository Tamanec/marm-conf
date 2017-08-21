<?php

namespace mc\models;


class ArchiveFactory {

    /**
     * @param string $fullFileName
     * @return \Archive_Tar
     */
    public function createArchive(string $fullFileName) : \Archive_Tar {
        return new \Archive_Tar($fullFileName, 'gz');
    }

}