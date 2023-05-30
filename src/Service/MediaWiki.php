<?php

namespace App\Service;

use DOMDocument;
use DOMNode;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

/**
 * OOP wrapper for the MediaWiki API
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

    protected function sendQuery(array $query): \stdClass
    {
        $response = $this->client->request('GET', $this->host, ['query' => $query]);

        if ($response->getStatusCode() !== 200) {
            throw new UnexpectedValueException('API returned ' . $response->getStatusCode() . ' status code');
        }

        return json_decode($response->getContent());
    }

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
        $elements = $xpath->query('//a[@class="image"]/img');
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

}
