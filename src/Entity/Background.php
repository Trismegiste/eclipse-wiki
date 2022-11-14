<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * Background for a transhuman
 */
class Background implements Indexable, Persistable
{

    use PersistableImpl;

    public ?string $title = null;
    public array $ability = [];
    public array $disability = [];
    public array $motivation = [];

    public function __construct(string $param)
    {
        $this->title = $param;
    }

    public function getUId(): string
    {
        return $this->title;
    }

}
