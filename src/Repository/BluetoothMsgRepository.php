<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use IteratorIterator;
use MongoDB\Driver\Command;
use MongoDB\Driver\Query;
use Trismegiste\Toolbox\MongoDb\DefaultRepository;

/**
 * Queue for bluetooth messaging
 */
class BluetoothMsgRepository extends DefaultRepository
{

    public function reset(): void
    {
        // drop
        $this->manager->executeCommand($this->dbName, new Command([
                'drop' => $this->collectionName
        ]));

        // create
        $this->manager->executeCommand($this->dbName, new Command([
                'create' => $this->collectionName,
                'capped' => true,
                'size' => 100000,
        ]));
    }

    public function getTailableCursor(): IteratorIterator
    {
        $query = new Query([], [
            'tailable' => true,
            'awaitData' => true,
        ]);

        $cursor = $this->manager->executeQuery($this->getNamespace(), $query);
        $iterator = new IteratorIterator($cursor);
        $iterator->rewind();

        return $iterator;
    }

}
