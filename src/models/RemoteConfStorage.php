<?php

namespace mc\models;


use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Model\BSONDocument;

/**
 * Class Configuration
 * @package mc\models
 */
class RemoteConfStorage {

    const BUFFER__MAX_SIZE = 1000;

    /**
     * @var string
     */
    private $project;

    /**
     * @var int
     */
    private $confVersion;
    
    /**
     * @var Client
     */
    private $client;

    /**
     * @var array[]
     */
    private $buffer;

    /**
     * Configuration constructor.
     * @param Client $client
     */
    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * @return \Generator|string[] Collection names
     */
    public function getCollections() {
        $collections = $this->getDb()->listCollections();
        foreach ($collections as $collectionInfo) {
            yield $collectionInfo->getName();
        }
    }

    /**
     * @param string $name Collection name
     * @return \Generator|array[] Document of collection
     */
    public function getCollectionData(string $name) {
        $collection = $this->getDb()->selectCollection($name);
        /** @var BSONDocument $data */
        foreach ($collection->find() as $data) {
            $conf = $data->getArrayCopy();
            $conf['id'] = (string) $conf['_id'];
            unset($conf['_id']);
            yield $conf;
        }
    }

    /**
     * update через upsert не работает из-за данных с ключами содержащими спец знаки: . $
     *
     * @param array $data
     * @param string $collectionName
     */
    public function save(array $data, string $collectionName) {
        if (isset($data['id'])) {
            $data['_id'] = new ObjectID($data['id']);
            unset($data['id']);
        }

        $this->buffer[] = $data;
        if (count($this->buffer) === self::BUFFER__MAX_SIZE) {
            $this->flush($collectionName);
        }
    }

    public function drop() {
        $this->getDb()->drop();
    }

    /**
     * Send buffered data to DB
     *
     * @param string $collectionName
     */
    public function flush(string $collectionName) {
        $collection = $this->getDb()->selectCollection($collectionName);
        $collection->deleteMany([
            '_id' => [
                '$in' => array_column($this->buffer, "_id")
            ]
        ]);
        $collection->insertMany($this->buffer);
        $this->buffer = [];
    }

    /**
     * @param string $project
     * @return RemoteConfStorage
     */
    public function setProject(string $project): RemoteConfStorage
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @param int $confVersion
     * @return RemoteConfStorage
     */
    public function setConfVersion(int $confVersion): RemoteConfStorage
    {
        $this->confVersion = $confVersion;
        return $this;
    }
    
    private function getDb(): Database {
        $dbName = "{$this->project}_{$this->confVersion}";
        return $this->client->{$dbName};
    }

}