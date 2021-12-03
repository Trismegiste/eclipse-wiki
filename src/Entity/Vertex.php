<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * A generic document or node 
 */
class Vertex implements \Trismegiste\Toolbox\MongoDb\Root
{

    use \Trismegiste\Toolbox\MongoDb\RootImpl;

    protected $title;
    protected $content = null;

    public function __construct(string $str)
    {
        $this->title = $str;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $str): void
    {
        $this->content = $str;
    }

    public function getCategory(): string
    {
        $fqcn = get_class($this);
        $match = [];
        preg_match('#([^\\\\]+)$#', $fqcn, $match);

        return strtolower($match[1]);
    }

    public function setTitle(string $newTitle): void
    {
        $this->title = $newTitle;
    }

}
