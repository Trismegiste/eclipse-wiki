<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * A damage roll in SaWo RPG
 */
class DamageRoll
{

    protected $diceCount = [4 => 0, 6 => 0, 8 => 0, 10 => 0, 12 => 0];
    protected $bonus = 0;

    public static function createFromString(string $roll)
    {
        $obj = new self();
        $dice = preg_split('#\s*\+\s*#', trim($roll));
        foreach ($dice as $oneDie) {
            $dump = [];
            preg_match('#^(?:([\d])d([\d]+))|(?:([\d]+))$#', $oneDie, $dump, PREG_UNMATCHED_AS_NULL);
            if (!is_null($dump[2])) {
                $obj->addDice($dump[2], $dump[1]);
            } else {
                $obj->incBonus($dump[3]);
            }
        }

        return $obj;
    }

    public function addDice(int $side, int $cnt): void
    {
        $this->diceCount[$side] += $cnt;
    }

    public function incBonus(int $param)
    {
        $this->bonus += $param;
    }

    public function getDiceCount(): array
    {
        return $this->diceCount;
    }

    public function getBonus(): int
    {
        return $this->bonus;
    }

    public function getDieCount(int $side): int
    {
        return $this->diceCount[$side];
    }

}
