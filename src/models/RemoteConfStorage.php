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
     * @var Database
     */
    private $db;

    /**
     * @var array[]
     */
    private $buffer;

    /**
     * Configuration constructor.
     * @param string $project
     * @param int $confVersion
     */
    public function __construct(string $project, int $confVersion) {
        $dbName = "{$project}_{$confVersion}";
        $this->db = (new Client("mongodb://{$_ENV['MONGO_USER']}:{$_ENV['MONGO_PASSWORD']}@{$_ENV['MONGO_SERVER']}/admin"))->{$dbName};
    }

    /**
     * @return \Generator|string[] Collection names
     */
    public function getCollections() {
        $collections = $this->db->listCollections();
        foreach ($collections as $collectionInfo) {
            yield $collectionInfo->getName();
        }
    }

    /**
     * @param string $name Collection name
     * @return \Generator|array[] Document of collection
     */
    public function getCollectionData(string $name) {
        $collection = $this->db->selectCollection($name);
        /** @var BSONDocument $data */
        foreach ($collection->find() as $data) {
            yield $data->getArrayCopy();
        }
    }

    /**
     * update через upsert не работает из-за данных с ключами содержащими спец знаки: . $
     *
     * @param array $data
     * @param string $collectionName
     */
    public function save(array $data, string $collectionName) {
        if (isset($data['_id'])) {
            $data['_id'] = new ObjectID($data['_id']);
        }

        $this->buffer[] = $data;
        if (count($this->buffer) === self::BUFFER__MAX_SIZE) {
            $this->flush($collectionName);
        }
    }

    public function drop() {
        $this->db->drop();
    }

    /**
     * Send buffered data to DB
     *
     * @param string $collectionName
     */
    public function flush(string $collectionName) {
        $collection = $this->db->selectCollection($collectionName);
        $collection->deleteMany([
            '_id' => [
                '$in' => array_column($this->buffer, "_id")
            ]
        ]);
        $collection->insertMany($this->buffer);
        $this->buffer = [];
    }

}