<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Symfony\Component\Finder\Finder;

/**
 * Folder for storing temporary files to be broadcasted to players
 */
class PlayerCastCache extends LocalFileCache
{

    public function getEpub(): iterable
    {
        return (new Finder())
                        ->in($this->folder)
                        ->files()
                        ->name('*.epub')
                        ->sortByName()
                        ->getIterator();
    }

}
