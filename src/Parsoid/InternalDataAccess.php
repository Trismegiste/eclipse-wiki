<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid;

use App\Repository\VertexRepository;
use App\Service\Storage;
use Wikimedia\Parsoid\Config\DataAccess;
use Wikimedia\Parsoid\Config\PageConfig;
use Wikimedia\Parsoid\Config\PageContent;
use Wikimedia\Parsoid\Core\ContentMetadataCollector;

/**
 * Description of InternalDataAccess
 *
 * @author flo
 */
class InternalDataAccess extends DataAccess
{

    public function __construct(protected VertexRepository $repositoy, protected Storage $storage)
    {
        
    }

    public function doPst(PageConfig $pageConfig, string $wikitext): string
    {
        return preg_replace('/\{\{subst:1x\|([^}]+)\}\}/', '$1', $wikitext, 1);
    }

    public function fetchTemplateData(PageConfig $pageConfig, string $title): ?array
    {
        return null;
    }

    private function normTitle(string $title): string
    {
        return strtr($title, ' ', '_');
    }

    public function fetchTemplateSource(PageConfig $pageConfig, string $title): ?PageContent
    {
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
                'descriptionurl' => '/',
                'mediatype' => 'BITMAP',
                'mime' => $pictureInfo['mime'],
                'badFile' => false,
            ];

            $ret[] = $info;
        }

        return $ret;
    }

    public function getPageInfo(PageConfig $pageConfig, array $titles): array
    {
        $ret = [];

        // database
        $iterator = $this->repositoy->search(['title' => ['$in' => $titles]], ['content']);
        foreach ($iterator as $vertex) {
            $ret[$vertex->getTitle()] = [
                'pageId' => $vertex->getPk(),
                'revId' => 1,
                'missing' => false,
                'known' => true,
                'redirect' => false,
                'linkclasses' => []
            ];
        }

        // fill the missing
        foreach ($titles as $title) {
            if (!key_exists($title, $ret)) {
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
        // nothing
    }

    public function parseWikitext(PageConfig $pageConfig, ContentMetadataCollector $metadata, string $wikitext): string
    {
        return '';
    }

    public function preprocessWikitext(PageConfig $pageConfig, ContentMetadataCollector $metadata, string $wikitext): string
    {
        return '';
    }

}
