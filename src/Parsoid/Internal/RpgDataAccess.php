<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid\Internal;

use App\Repository\VertexRepository;
use App\Service\Storage;
use Wikimedia\Parsoid\Config\DataAccess;
use Wikimedia\Parsoid\Config\PageConfig;
use Wikimedia\Parsoid\Config\PageContent;
use Wikimedia\Parsoid\Core\ContentMetadataCollector;
use Wikimedia\Parsoid\Core\LinkTarget;

/**
 * Description of RpgDataAccess
 *
 * @author trismegiste
 */
class RpgDataAccess extends DataAccess
{

    public function __construct(protected VertexRepository $repositoy, protected Storage $storage)
    {
        
    }

    public function doPst(PageConfig $pageConfig, string $wikitext): string
    {
        
    }

    public function fetchTemplateData(PageConfig $pageConfig, LinkTarget $title): ?array
    {
        
    }

    public function fetchTemplateSource(PageConfig $pageConfig, LinkTarget $title): ?PageContent
    {
        if (preg_match('#^template:(.+)$#', $title, $matches)) {
            if (key_exists($matches[1], self::template)) {
                return new \Wikimedia\Parsoid\Mocks\MockPageContent(['main' => self::template[$matches[1]]]);
            }
        }

        return null;
    }

    public function getFileInfo(PageConfig $pageConfig, array $files): array
    {
        
    }

    public function getPageInfo($pageConfigOrTitle, array $titles): array
    {
        $ret = [];
        foreach ($titles as $title) {
            // @todo shortcut for template
            $found = $this->repositoy->findByTitle($title);
            if ($found) {
                $ret[$title] = [
                    'pageId' => $found->getPk(),
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
