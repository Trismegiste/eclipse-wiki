<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use JsonSerializable;
use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;
use UnexpectedValueException;

/**
 * Hindrance 
 */
class Hindrance extends Modifier implements Persistable, Indexable, JsonSerializable
{

    use PersistableImpl;

    const MINOR = 1;
    const MAJOR = 2;
    const MINOR_MAJOR = 3;

    protected $choices;
    protected $level;
    public $origin = null; // creation, gift, xperience, morph, morph slot...

    public function __construct(string $str, $ego = false, $biomorph = false, $synthmorph = false, $choices = self::MINOR_MAJOR)
    {
        parent::__construct($str, $ego, $biomorph, $synthmorph);
        $this->choices = $choices;
    }

    public function jsonSerialize()
    {
        return $this->bsonSerialize();
    }

    /**
     * @return int one of MINOR or MAJOR or bit combinations
     */
    public function getChoices(): int
    {
        return $this->choices;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $param): void
    {
        if (!($param & $this->choices)) {
            throw new UnexpectedValueException("$param is not a valid value for a Hindrance level");
        }
        $this->level = $param;
    }

}
