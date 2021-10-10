<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;

/**
 * A SaWo Skill
 */
class Skill extends SaWoTrait implements Persistable
{

    use PersistableImpl;

    protected $linkedAttr;
    protected $core;

    public function __construct(string $str, string $linkAttr, bool $core = false)
    {
        parent::__construct($str);
        $this->linkedAttr = $linkAttr;
        $this->core = $core;
    }

}
