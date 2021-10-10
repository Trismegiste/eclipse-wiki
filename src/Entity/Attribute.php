<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;

/**
 * One SaWo Attribute
 */
class Attribute implements Persistable
{

    use PersistableImpl;

    protected $name;
    public $dice;
    public $modifier = 0; // after d12

    public function __construct(string $str)
    {
        $this->name = $str;
    }

    public function getName(): string
    {
        return $this->name;
    }

}
