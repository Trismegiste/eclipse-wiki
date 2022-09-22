<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Root class for Edge and Hindrance
 */
abstract class Modifier implements Indexable
{

    protected string $name;
    protected bool $ego;
    protected bool $biomorph;
    protected bool $synthmorph;

    public function __construct(string $str, bool $ego = false, bool $biomorph = false, bool $synthmorph = false)
    {
        $this->name = $str;
        $this->ego = $ego;
        $this->biomorph = $biomorph;
        $this->synthmorph = $synthmorph;
    }

    public function getUId(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isBio(): bool
    {
        return $this->biomorph;
    }

    public function isSynth(): bool
    {
        return $this->synthmorph;
    }

    public function isEgo(): bool
    {
        return $this->ego;
    }

}
