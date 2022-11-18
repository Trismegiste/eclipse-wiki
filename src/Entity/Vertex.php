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

    /**
     * Gets the title of this vertex
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Gets the content of this vertex (could be empty)
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Sets the content
     * @param string $str
     * @return void
     */
    public function setContent(string $str): void
    {
        $this->content = $str;
    }

    /**
     * Gets a technical string for the category of this vertex
     * @return string
     */
    public function getCategory(): string
    {
        $fqcn = get_class($this);
        $match = [];
        preg_match('#([^\\\\]+)$#', $fqcn, $match);

        return strtolower($match[1]);
    }

    /**
     * Sets a new title for this Vertex
     * WARNING : do not call this method unless you rename all links in all vertices inbound to this vertex
     * @param string $newTitle
     * @return void
     */
    public function setTitle(string $newTitle): void
    {
        $this->title = $newTitle;
    }

    /**
     * Gets the first picture in the content (or not)
     * @return string|null
     */
    public function extractFirstPicture(): ?string
    {
        $picture = $this->extractPicture();

        return count($picture) ? $picture[0] : null;
    }

    /**
     * Gets all pictures in the content
     * @return array
     */
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

    /**
     * Gets all links (outbound) from this vertex to another (existing or not) by exploring the content
     * @return array
     */
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
