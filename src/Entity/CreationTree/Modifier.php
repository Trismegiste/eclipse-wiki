<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\CreationTree;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * Given bonus for a creation node
 */
class Modifier implements Persistable
{

    use PersistableImpl;

    public function increaseBy(Modifier $rh): Modifier
    {
        // suming traits
        $this->attributes = static::arrayValueSum($this->attributes, $rh->attributes);
        $this->skills = static::arrayValueSum($this->skills, $rh->skills);
        $this->networks = static::arrayValueSum($this->networks, $rh->networks);
        // cumulative bonus
        foreach ($rh->edges as $title) {
            $this->edges[] = $title;
        }
        $this->edges = array_unique($this->edges);
    }

    static public function arrayValueSum(array $lh, array $rh): array
    {
        $sum = [];
        $keys = array_keys($lh) + array_keys($rh);
        foreach ($keys as $key) {
            $sum[$key] = 0;
            if (key_exists($key, $lh)) {
                $sum[$key] += $lh[$key];
            }
            if (key_exists($key, $rh)) {
                $sum[$key] += $rh[$key];
            }
        }

        return $sum;
    }

}
