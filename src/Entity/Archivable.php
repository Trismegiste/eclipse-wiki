<?php

/*
 * Eclipse-wiki
 */

namespace App\Entity;

/**
 * Is entity archivable ?
 */
interface Archivable
{

    public function setArchived(bool $val): void;

    public function getArchived(): bool;
}
