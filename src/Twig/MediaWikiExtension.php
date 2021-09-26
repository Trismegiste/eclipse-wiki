<?php

namespace App\Twig;

use App\Service\MediaWiki;
use Twig\Extension\AbstractExtension;

/**
 * Description of MediaWikiExtension
 */
class MediaWikiExtension extends AbstractExtension
{

    protected $api;

    public function __construct(MediaWiki $api)
    {
        $this->api = $api;
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('dump_page', [$this, 'dumpPage'], ['is_safe' => ['html']]),
            new \Twig\TwigFunction('dump_category', [$this, 'dumpCategory'], ['is_safe' => ['html']])
        ];
    }

    public function dumpCategory(string $category, int $limit = 30): string
    {
        $page = $this->api->searchPageFromCategory($category, $limit);

        $dump = '<article>' . $this->api->getPageByName("Catégorie:$category") . "</article>\n";
        foreach ($page as $item) {
            $title = $item->title;
            $content = $this->api->getPage($item->pageid);
            $dump .= "<article>\n";
            $dump .= "<h1>$title</h1>\n";
            $dump .= $content;
            $dump .= "</article>\n";
            usleep(100000);
        }

        return $dump;
    }

    public function dumpPage(string $title): string
    {
        $content = $this->api->getPageByName($title);

        return "<article><h1>$title</h1>\n$content</article>\n";
    }

}
