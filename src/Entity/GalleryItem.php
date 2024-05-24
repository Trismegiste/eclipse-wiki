<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use MongoDB\BSON\ObjectIdInterface;

/**
 * a Vertex entry in gallery
 * @todo should implement JsonSerializable if ajaxed
 */
class GalleryItem
{

    public readonly string $title;
    public readonly ObjectIdInterface $pk;
    public array $picture = [];
    public string $thumb;
    public string $push;
    public string $classname;

    public function __construct(Vertex $vertex, public readonly string $icon)
    {
        $this->pk = $vertex->getPk();
        $this->title = $vertex->getTitle();
        $this->picture = $vertex->extractPicture();
    }

    public function hasThumbnail(): bool
    {
        return isset($this->thumb);
    }

    public function isPushable(): bool
    {
        return $this->hasThumbnail() && isset($this->push);
    }

}
