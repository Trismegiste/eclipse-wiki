<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;

/**
 * A SaWo Attribute
 */
class Attribute extends SaWoTrait implements Persistable
{

    use PersistableImpl;
}
