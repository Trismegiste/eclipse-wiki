<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * MediaWiki API
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
            throw new \UnexpectedValueException('API returned ' . $response->getStatusCode() . ' status code');
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

}
