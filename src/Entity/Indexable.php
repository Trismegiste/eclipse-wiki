<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Contract : this class has a unique index
 */
interface Indexable
{

    public function getUId(): string;
}
