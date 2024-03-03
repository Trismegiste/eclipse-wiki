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

    protected string $title;
    protected string $category;
    public string $content;

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

    public function getPurifiedContentForLocalRendering(): string
    {
        return preg_replace(
                [
                    '#\[\[Fichier\:([^\]]+)\]\]#',  // remove pictures
                    '#\[\[Catégorie\:([^]]+)\]\]#', // remove categories
                    '#\{\{([^\}]+)\}\}#', // remove template transclusions
                    '#\[\[([^\]\|]+)\]\]#', // replace simple links by the link text
                    '#\[\[[^\|\]]+\|([^\]]+)\]\]#',  // replace links with title by the title
                    '#__notoc__#',
                ],
                [
                    '',
                    '',
                    '',
                    "'''\$1'''",
                    "'''\$1'''",
                    '',
                ],
                $this->content);
    }

}
