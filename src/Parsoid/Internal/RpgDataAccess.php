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

    const template = [
        'legend' => '{{{1}}}<i class="icon-view3d" data-cell-index="{{{2}}}"></i>',
        'roll' => '',
        'morphbank' => '{{{1}}}<i data-pushable="pdf" class="icon-push" data-title="{{{1}}}"></i>'
    ];

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
