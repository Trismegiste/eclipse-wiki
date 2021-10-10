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

    public function __construct(string $str, string $attrAbbrev)
    {
        parent::__construct($str);
        $this->linkedAttr = $attrAbbrev;
    }

}
