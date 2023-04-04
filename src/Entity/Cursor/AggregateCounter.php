<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Cursor;

use App\Entity\Vertex;
use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * Cursor for counting entity per class
 */
class AggregateCounter implements Persistable
{

    use PersistableImpl;

    public string $fqcn;
    public int $total;
    public int $archived;

    public function getCategory(): string
    {
        return Vertex::getCategoryForVertex($this->fqcn);
    }

}
