<?php

/*
 * Eclipse-wiki
 */

namespace App\Entity;

/**
 * Is entity archivable ?
 */

trait ArchivableImpl
{

    protected bool $archived = false;

    public function setArchived(bool $val): void
    {
        $this->archived = $val;
    }

    public function getArchived(): bool
    {
        return $this->archived;
    }

}
