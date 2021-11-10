<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use MongoDB\BSON\Regex;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * Description of VertexRepository
 *
 * @author flo
 */
class VertexRepository
{

    protected $collection;

    public function __construct(Repository $documentRepo)
    {
        $this->collection = $documentRepo;
    }

    public function findByTitle(string $title)
    {
        $tmp = preg_split('//u', $title, null, PREG_SPLIT_NO_EMPTY);
        $firstLetter = array_shift($tmp);

        $it = $this->collection->search([
            'title' => new Regex("(?i:$firstLetter)" . implode('', $tmp))
        ]);
        $it->rewind();

        return $it->current();
    }

}
