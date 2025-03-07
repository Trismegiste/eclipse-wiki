<?php

namespace App\Service;

use App\Entity\MediaWikiPage;
use DOMDocument;
use DOMNode;
use RuntimeException;
use stdClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

/**
 * HTTP Client for the MediaWiki API
 */
class MediaWiki
{

    protected $client;
    protected $host;

    public function __construct(HttpClientInterface $cl, string $host)
    {
        $this->client = $cl;
        $this->host = "https://$host/fr/api.php";
    }

    public function searchPageFromCategory(string $cat, int $lim = 20): array
    {
        $response = $this->sendQuery([
            'action' => 'query',
            'format' => 'json',
            'list' => 'categorymembers',
            'cmtitle' => 'Catégorie:' . $cat,
            'cmlimit' => $lim
        ]);

        return $response->query->categorymembers;
    }

    public function getPage(int $id): string
    {
        return $this->getPageBy('pageid', $id);
    }

    public function getPageByName(string $name): string
    {
        return $this->getPageBy('page', $name);
    }

    public function getDocumentByName(string $name): DOMDocument
    {
        $content = $this->getPageByName($name);
        $doc = new DOMDocument("1.0", "UTF-8");
        libxml_use_internal_errors(true); // because other xml/svg namespace warning
        $doc->loadHTML('<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"></head><body>' . $content . '</body></html>');

        return $doc;
    }

    protected function getPageBy(string $field, string $value): string
    {
        $response = $this->sendQuery([
            'action' => 'parse',
            'format' => 'json',
            $field => $value,
            'prop' => 'text',
            'disablelimitreport' => 1,
            'disableeditsection' => 1,
            'disabletoc' => 1
        ]);

        return $response->parse->text->{'*'};
    }

    protected function sendQuery(array $query): stdClass
    {
        $response = $this->client->request('GET', $this->host, ['query' => $query]);

        if ($response->getStatusCode() !== 200) {
            throw new UnexpectedValueException('API returned ' . $response->getStatusCode() . ' status code');
        }

        return json_decode($response->getContent());
    }

    /**
     * Gets the wikitext code for a given page
     * @param string $name
     * @return string
     */
    public function getWikitextByName(string $name): string
    {
        $response = $this->sendQuery([
            'action' => 'parse',
            'format' => 'json',
            'page' => $name,
            'prop' => 'wikitext'
        ]);

        return $response->parse->wikitext->{'*'};
    }

    /**
     * Gets the wikitext code for a given page id
     * @param int $id
     * @return MediaWikiPage
     */
    public function getWikitextById(int $id): MediaWikiPage
    {
        $response = $this->sendQuery([
            'action' => 'parse',
            'format' => 'json',
            'pageid' => $id,
            'prop' => 'wikitext'
        ]);

        $page = new MediaWikiPage($response->parse->title, 'fandom');
        $page->content = $response->parse->wikitext->{'*'};

        return $page;
    }

    /**
     * Gets the XML Parsed Tree for a given page
     * @param string $name
     * @return string
     */
    public function getTreeAndHtmlDomByName(string $name): array
    {
        $response = $this->sendQuery([
            'action' => 'parse',
            'format' => 'json',
            'page' => $name,
            'prop' => 'parsetree|text',
            'disablelimitreport' => 1,
            'disableeditsection' => 1,
            'disabletoc' => 1
        ]);

        libxml_use_internal_errors(true); // because other xml/svg namespace warning
        $html = new DOMDocument("1.0", "UTF-8");
        $html->loadHTML('<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"></head><body>' .
                $response->parse->text->{'*'} .
                '</body></html>');

        $tree = new DOMDocument("1.0", "UTF-8");
        $tree->loadXML($response->parse->parsetree->{'*'});

        return ['tree' => $tree, 'html' => $html];
    }

    public function getTemplateData(string $templateName): array
    {
        $res = $this->sendQuery([
            'action' => 'templatedata',
            'format' => 'json',
            'titles' => 'Modèle:' . $templateName
        ]);

        $templateKey = array_key_first(get_object_vars($res->pages));
        $templateData = $res->pages->$templateKey;
        if ($templateData->title !== 'Modèle:' . $templateName) {
            throw new RuntimeException('Something went wrong');
        }

        return get_object_vars($templateData->params);
    }

    public function renderTemplate(string $templateName, string $title, array $parameters): string
    {
        $compil = '';
        foreach ($parameters as $k => $v) {
            $compil .= "|$k=$v";
        }
        $response = $this->sendQuery([
            'action' => 'parse',
            'format' => 'json',
            'title' => $title,
            'text' => '{{Modèle:' . $templateName . $compil . '}}',
            'disablelimitreport' => 1,
            'disableeditsection' => 1,
            'disabletoc' => 1
        ]);

        return $response->parse->text->{'*'};
    }

    // https://www.mediawiki.org/wiki/API:Search
    public function searchImage(string $txt, int $limit = 10): array
    {
        $res = $this->sendQuery([
            'action' => 'query',
            'format' => 'json',
            'list' => 'search',
            'srsearch' => $txt,
            'srnamespace' => 6,
            'srlimit' => $limit
        ]);

        return property_exists($res, 'query') ? $res->query->search : [];
    }

    public function renderGallery(array $listing): string
    {
        $wikitext = '';
        foreach ($listing as $picture) {
            $wikitext .= "[[{$picture->title}|vignette]]";
        }

        $response = $this->sendQuery([
            'action' => 'parse',
            'format' => 'json',
            'title' => 'Gallery',
            'text' => $wikitext,
            'disablelimitreport' => 1,
            'disableeditsection' => 1,
            'disabletoc' => 1
        ]);

        return $response->parse->text->{'*'};
    }

    public function extractUrlFromGallery(string $htmlContent): array
    {
        $extract = [];

        $content = strip_tags($htmlContent, '<a><div><figure><img>');
        $doc = new DOMDocument("1.0", "utf-8");
        libxml_use_internal_errors(true); // because other xml/svg namespace warning
        $doc->loadHTML($content);
        $xpath = new \DOMXpath($doc);
        $elements = $xpath->query('//a[@class="mw-file-description image"]/img');
        foreach ($elements as $img) {
            /** @var DOMNode $img */
            $thumbnail = $img->attributes->getNamedItem('src')->value;
            if (0 === strpos($thumbnail, 'http')) {
                $extract[] = (object) [
                            'thumbnail' => $thumbnail,
                            'original' => $img->parentNode->attributes->getNamedItem('href')->value
                ];
            }
        }

        return $extract;
    }

    public function searchPageByName(string $name): array
    {
        return $this->sendQuery([
                    'action' => 'query',
                    'srsearch' => $name,
                    'list' => 'search',
                    'format' => 'json'
                ])->query->search;
    }

    public function prefixSearch(string $q, int $limit = 5): array
    {
        return $this->sendQuery([
            'action' => 'query',
            'format' => 'json',
            'list' => 'prefixsearch',
            'pssearch' => $q,
            'pslimit' => $limit
        ])->query->prefixsearch;
    }

}
