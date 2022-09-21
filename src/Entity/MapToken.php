<?php

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * A generic movable token on a battlemap
 */
class MapToken implements Persistable {

    use PersistableImpl;

    public string $picture;
    public string $label;

    public function __construct(string $img, string $label)
    {
        $this->picture = $img;
        $this->label = $label;
    }
}
