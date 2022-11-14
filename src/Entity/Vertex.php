<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\UTCDateTime;
use Trismegiste\Strangelove\MongoDb\Root;
use Trismegiste\Strangelove\MongoDb\RootImpl;

/**
 * A generic document or node 
 */
abstract class Vertex implements Root, Archivable
{

    use RootImpl;
    use ArchivableImpl;

    protected string $title;
    protected ?string $content = null;
    protected $lastModified;

    protected function beforeSave(): void
    {
        $this->lastModified = new UTCDateTime();
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
        $picture = $this->extractPicture();

        return count($picture) ? $picture[0] : null;
    }

    public function extractPicture(): array
    {
        if (is_null($this->content)) {
            return [];
        }

        $matches = [];
        preg_match_all('#\[\[file:([^\]]+)\]\]#', $this->getContent(), $matches, PREG_SET_ORDER, 0);

        return array_column($matches, 1);
    }

    public function __clone()
    {
        $this->_id = null;
    }

    public function getInternalLink(): array
    {
        $re = '/\[\[([^\|\]]+)(\]\]|\|)/m';
        $matches = [];
        preg_match_all($re, $this->content, $matches, PREG_SET_ORDER, 0);

        return array_filter(array_column($matches, 1), function ($val) {
            return false === strpos($val, ':');
        });
    }

}
