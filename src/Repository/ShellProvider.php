<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

/**
 * Description of ShellProvider
 */
class ShellProvider extends CachedProvider
{

    public function findOne(string $key): \App\Entity\Indexable
    {
        return new \App\Entity\Morph('yolo');
    }

    public function getListing(): array
    {
        return ['yolo' => 'Yolo'];
    }

}
