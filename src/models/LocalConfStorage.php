<?php

namespace mc\models;


use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class LocalConfStorage implements LocalStorage {

    /**
     * @var string Folder with projects
     */
    private $path;

    /**
     * @var string Path to project
     */
    private $projectPath;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var FileNameRules
     */
    private $fileNameRules;

    /**
     * @var string
     */
    private $project;

    /**
     * LocalConfStorage constructor.
     * @param Filesystem $fs
     */
    public function __construct(Filesystem $fs) {
        $this->fs = $fs;
    }

    public function initProject() {
        $this->projectPath = $this->path . DIRECTORY_SEPARATOR . $this->project;
        $this->fs->remove($this->projectPath);
        $this->fs->mkdir($this->projectPath);
        $this->fs->chmod($this->projectPath, 0777);
    }

    /**
     * @param string $name Collection name
     */
    public function initCollection(string $name) {
        $this->fs->mkdir($this->projectPath . DIRECTORY_SEPARATOR . $name);
        $this->fs->chmod($this->projectPath . DIRECTORY_SEPARATOR . $name, 0777);
    }

    /**
     * @param array $data
     * @param string $collectionName
     */
    public function save(array $data, string $collectionName) {
        $fullName = $this->projectPath . DIRECTORY_SEPARATOR
            . $collectionName . DIRECTORY_SEPARATOR
            . $this->fileNameRules->getFileName($collectionName, $data);
        file_put_contents($fullName, json_encode($data, JSON_PRETTY_PRINT));
        $this->fs->chmod($fullName, 0777);
    }

    /**
     * @return \Generator|string[] Collection names
     */
    public function getCollections() {
        $iterator = (new Finder())
            ->directories()
            ->in($this->path . DIRECTORY_SEPARATOR . $this->project)
            ->depth(0)
        ;
        /** @var SplFileInfo $dir */
        foreach ($iterator as $dir) {
            yield $dir->getFilename();
        }
    }

    /**
     * @param string $name Collection name
     * @return \Generator|array[] Document of collection
     */
    public function getCollectionData(string $name) {
        $iterator = (new Finder())
            ->files()
            ->in($this->path . DIRECTORY_SEPARATOR . $this->project . DIRECTORY_SEPARATOR . $name)
            ->depth(0)
        ;
        /** @var SplFileInfo $data */
        foreach ($iterator as $data) {
            yield json_decode($data->getContents(), true);
        }
    }

    /**
     * @param string $project
     */
    public function remove($project) {
        $this->fs->remove($this->path . DIRECTORY_SEPARATOR . $project);
    }

    /**
     * @param string $path
     * @return LocalConfStorage
     */
    public function setPath(string $path): LocalConfStorage
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * @param string $project
     * @return LocalConfStorage
     */
    public function setProject(string $project): LocalConfStorage
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @param FileNameRules $fileNameRules
     * @return LocalConfStorage
     */
    public function setFileNameRules(FileNameRules $fileNameRules): LocalConfStorage {
        $this->fileNameRules = $fileNameRules;
        return $this;
    }

}