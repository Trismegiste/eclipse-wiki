<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid\Internal;

use App\Repository\VertexRepository;
use App\Service\Storage;
use DateInterval;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Wikimedia\Parsoid\Config\DataAccess;
use Wikimedia\Parsoid\Config\PageConfig;
use Wikimedia\Parsoid\Config\PageContent;
use Wikimedia\Parsoid\Core\ContentMetadataCollector;
use Wikimedia\Parsoid\Core\LinkTarget;

/**
 * Repository for MediaWiki bridging with Vertex repository and Storage service
 */
class RpgDataAccess extends DataAccess
{

    public function __construct(protected VertexRepository $repositoy,
            protected Storage $storage,
            protected CacheInterface $parsoidCache,
            protected string $templateFolder)
    {
        
    }

    public function doPst(PageConfig $pageConfig, string $wikitext): string
    {
        
    }

    public function fetchTemplateData(PageConfig $pageConfig, LinkTarget $title): ?array
    {
        
    }

    /**
     * Returns a template content by its name (cached)
     * @param PageConfig $pageConfig
     * @param LinkTarget $title
     * @return PageContent|null
     */
    public function fetchTemplateSource(PageConfig $pageConfig, LinkTarget $title): ?PageContent
    {
        return $this->parsoidCache->get($title->getDBkey(), function (ItemInterface $item)use ($title) {
                    $item->expiresAfter(DateInterval::createFromDateString('1 day'));
                    $tmp = $this->templateFolder . '/' . $title->getDBkey() . '.wikitext';

                    return file_exists($tmp) ? new RpgPageContent(file_get_contents($tmp)) : null;
                });
    }

    /**
     * Returns an array of informations for a given list of pictures, by name
     * @param PageConfig $pageConfig
     * @param array $files
     * @return array
     */
    public function getFileInfo(PageConfig $pageConfig, array $files): array
    {
        $ret = [];
        foreach ($files as $source) {
            $filename = str_replace('_', ' ', $source[0]);
            $srcInfo = $this->storage->getFileInfo($filename);
            if (!$srcInfo->isReadable()) {
                $ret[] = null;
                continue;
            }

            $pictureInfo = getimagesize($srcInfo->getPathname());
            $info = [
                'size' => $srcInfo->getSize(),
                'height' => $pictureInfo[1],
                'width' => $pictureInfo[0],
                'url' => "/picture/get/" . $filename,
                'mediatype' => 'BITMAP',
                'mime' => $pictureInfo['mime'],
                'badFile' => false
            ];

            $ret[] = $info;
        }

        return $ret;
    }

    /**
     * Returns an array of informations for a given list of pages, by name
     * @param type $pageConfigOrTitle
     * @param array $titles
     * @return array
     */
    public function getPageInfo($pageConfigOrTitle, array $titles): array
    {
        $iter = $this->repositoy->searchPkByTitle($titles);
        $ret = [];

        foreach ($iter as $title => $pk) {
            if (!is_null($pk)) {
                $ret[$title] = [
                    'pageId' => $pk,
                    'revId' => 1,
                    'missing' => false,
                    'known' => true,
                    'redirect' => false,
                    'linkclasses' => []
                ];
            } else {
                $ret[$title] = [
                    'pageId' => null,
                    'revId' => null,
                    'missing' => true,
                    'known' => false,
                    'redirect' => false,
                    'linkclasses' => [],
                ];
            }
        }

        return $ret;
    }

    public function logLinterData(PageConfig $pageConfig, array $lints): void
    {
        
    }

    public function parseWikitext(PageConfig $pageConfig, ContentMetadataCollector $metadata, string $wikitext): string
    {
        
    }

    public function preprocessWikitext(PageConfig $pageConfig, ContentMetadataCollector $metadata, string $wikitext): string
    {
        
    }

}
