<?php

namespace mc\models;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class LocalConfStorage {

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
     * @var string
     */
    private $project;

    /**
     * ConfigurationStorage constructor.
     * @param Filesystem $fs
     */
    public function __construct(Filesystem $fs) {
        $this->fs = $fs;
    }

    public function initProject() {
        $this->projectPath = $this->path . DIRECTORY_SEPARATOR . $this->project;
        $this->fs->remove($this->projectPath);
        $this->fs->mkdir($this->projectPath);
    }

    /**
     * @param string $name Collection name
     */
    public function initCollection(string $name) {
        $this->fs->mkdir($this->projectPath . DIRECTORY_SEPARATOR . $name);
    }

    /**
     * @param array $data
     * @param string $collectionName
     */
    public function save(array $data, string $collectionName) {
        $data['_id'] = (string) $data['_id'];
        $fullName = $this->projectPath . DIRECTORY_SEPARATOR
            . $collectionName . DIRECTORY_SEPARATOR
            . $this->getFileName($data, $collectionName);
        file_put_contents($fullName, json_encode($data));
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
     * @param array $data
     * @param string $collectionName
     * @return string
     */
    protected function getFileName(array $data, string $collectionName): string {
        switch ($collectionName) {
            case 'views':
                $fileName = $data['name'];
                break;

            case 'templates':
                $fileName = "{$data['class']}-{$data['name']}";
                break;

            case 'scripts':
                $fileName = "{$data['type']}-{$data['name']}";
                break;

            case 'styles':
                $fileName = "{$data['type']}-{$data['name']}-{$data['template']}-{$data['_id']}";
                break;

            case 'rules':
                if ($data['class'] === 'view') {
                    $fileName = "{$data['class']}-{$data['viewName']}-{$data['_id']}";
                } elseif ($data['class'] === 'doc') {
                    $fileName = "{$data['class']}-{$data['type']}-{$data['_id']}";
                } else {
                    $models = implode(',', (array)$data['models']);
                    $methods = implode(',', (array)$data['methods']);
                    $fileName = "{$data['class']}-{$models}-{$methods}-{$data['_id']}";
                }

                break;

            default:
                $fileName = $data['_id'];
        }

        return $fileName;
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
     * @param string $project
     * @return LocalConfStorage
     */
    public function setProject(string $project): LocalConfStorage
    {
        $this->project = $project;
        return $this;
    }

}