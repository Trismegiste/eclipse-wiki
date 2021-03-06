<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * A page from MediaWiki
 */
class MediaWikiPage implements \Trismegiste\Strangelove\MongoDb\Root
{

    use \Trismegiste\Strangelove\MongoDb\RootImpl;

    protected $title;
    protected $category;
    public $content;

    public function __construct(string $title, string $category)
    {
        $this->title = $title;
        $this->category = $category;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

}
