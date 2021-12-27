<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

/**
 * Queue for bluetooth messaging
 */
class BluetoothMsgRepository extends \Trismegiste\Toolbox\MongoDb\DefaultRepository
{

    public function reset(): void
    {
        // drop
        $this->manager->executeCommand($this->dbName, new \MongoDB\Driver\Command([
                'drop' => $this->collectionName
        ]));

        // create
        $this->manager->executeCommand($this->dbName, new \MongoDB\Driver\Command([
                'create' => $this->collectionName,
                'capped' => true,
                'size' => 100000,
        ]));
    }

    public function getTailableCursor(): \IteratorIterator
    {
        $query = new \MongoDB\Driver\Query([], [
            'tailable' => true,
            'awaitData' => true,
        ]);

        $cursor = $this->manager->executeQuery($this->getNamespace(), $query);
        $iterator = new \IteratorIterator($cursor);
        $iterator->rewind();

        return $iterator;
    }

}
