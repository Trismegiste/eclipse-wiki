<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * A generic document or node 
 */
class Vertex implements \Trismegiste\Strangelove\MongoDb\Root
{

    use \Trismegiste\Strangelove\MongoDb\RootImpl;

    protected $title;
    protected $content = null;
    protected $lastModified;
    protected $archived = false;

    protected function beforeSave(): void
    {
        $this->lastModified = new \MongoDB\BSON\UTCDateTime();
    }

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

    public function extractFirstPicture(): ?string
    {
        if (is_null($this->getContent())) {
            return null;
        }

        if (preg_match('#\[\[file:([^\]]+)\]\]#', $this->getContent(), $match)) {
            return $match[1];
        }

        return null;
    }

    public function __clone()
    {
        $this->_id = null;
    }

    public function setArchived(bool $val): void
    {
        $this->archived = $val;
    }

    public function getArchived(): bool
    {
        return $this->archived;
    }
}
