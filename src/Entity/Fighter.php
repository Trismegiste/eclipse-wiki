<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * This is a generic fighter
 */
interface Fighter
{

    public function getToughness(): int;

    public function getTotalArmor(): int;

    public function getParry(): int;

    public function getTitle(): string;

    public function isWildcard(): bool;

    public function getMalusAgainstRangedAttack(): int;

    public function getTokenPicture(): ?string;

}
