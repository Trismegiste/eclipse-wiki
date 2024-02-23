<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * A wrapper for a Fighter that add JSON serialization features
 * Design pattern : Decorator
 * Design intent : Reduce the amount of data for sending a Character through ajax
 * #projection #json #ajax #decorator
 */
class FighterJsonDecorator implements Fighter, \JsonSerializable
{

    public function __construct(protected Fighter $wrapped)
    {
        
    }

    public function getMalusAgainstRangedAttack(): int
    {
        return $this->wrapped->getMalusAgainstRangedAttack();
    }

    public function getParry(): int
    {
        return $this->wrapped->getParry();
    }

    public function getTitle(): string
    {
        return $this->wrapped->getTitle();
    }

    public function getTotalArmor(): int
    {
        return $this->wrapped->getTotalArmor();
    }

    public function getToughness(): int
    {
        return $this->wrapped->getToughness();
    }

    public function isWildcard(): bool
    {
        return $this->wrapped->isWildcard();
    }

    public function getTokenPicture(): ?string
    {
        return $this->wrapped->getTokenPicture();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'ranged' => $this->getMalusAgainstRangedAttack(),
            'toughness' => $this->getToughness(),
            'parry' => $this->getParry(),
            'wildcard' => $this->isWildcard(),
            'title' => $this->getTitle(),
            'armor' => $this->getTotalArmor(),
            'token' => $this->getTokenPicture()
        ];
    }

}
