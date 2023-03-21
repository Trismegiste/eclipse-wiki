<?php

namespace App\Twig;

use App\Service\MediaWiki;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Aggregator of rendered wikitext pages
 */
class MediaWikiExtension extends AbstractExtension
{

    protected $api;
    protected $wikiSource;

    public function __construct(MediaWiki $api, string $src)
    {
        $this->api = $api;
        $this->wikiSource = $src;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('dump_page', [$this, 'dumpPage'], ['is_safe' => ['html']]),
            new TwigFunction('dump_category', [$this, 'dumpCategory'], ['is_safe' => ['html']]),
            new TwigFunction('wikilink', [$this, 'externalWikiLink']),
        ];
    }

    public function dumpCategory(string $category, int $limit = 30): string
    {
        $page = $this->api->searchPageFromCategory($category, $limit);

        $dump = '<article>' . $this->api->getPageByName("Catégorie:$category") . "</article>\n";
        foreach ($page as $item) {
            $title = $item->title;
            $anchor = $this->getAnchorFor($title);
            $content = $this->api->getPage($item->pageid);
            $dump .= "<article>\n";
            $dump .= "$anchor<h1>$title</h1>\n";
            $dump .= $content;
            $dump .= "</article>\n";
            usleep(100000);
        }

        return $dump;
    }

    public function dumpPage(string $title): string
    {
        $content = $this->api->getPageByName($title);
        $anchor = $this->getAnchorFor($title);

        return "<article>\n$anchor<h1>$title</h1>\n$content\n</article>\n";
    }

    public function externalWikiLink(string $key): string
    {
        return "https://{$this->wikiSource}/fr/wiki/$key";
    }

    protected function getAnchorFor(string $title): string
    {
        $id = preg_replace('#[^_A-Za-z0-9]#', '.', urlencode(str_replace(' ', '_', $title)));

        return "<a id=\"$id\"></a>";
    }

}
