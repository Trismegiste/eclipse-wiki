<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use DateInterval;
use stdClass;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

/**
 * Client for InvokeAI Stable Diffusion
 */
class InvokeAi
{

    const BATCH_SIZE = 100;

    public function __construct(protected HttpClientInterface $client, protected string $baseUrl, protected CacheInterface $invokeaiCache)
    {
        
    }

    public function searchPicture(string $query, int $capFound = 10): array
    {
        $keywords = explode(' ', $query);
        $keywordCount = count($keywords);
        $found = [];
        $offset = 0;

        do {
            $resp = $this->getImagesList($offset, self::BATCH_SIZE);
            foreach ($resp->items as $picture) {
                $prompt = $this->searchPromptFor($picture->image_name);
                if (count(array_intersect($keywords, $prompt)) === $keywordCount) {
                    $found[] = (object) [
                                'full' => $this->baseUrl . $picture->image_url,
                                'thumb' => $this->baseUrl . $picture->thumbnail_url,
                                'width' => $picture->width
                    ];
                }
            }
            $offset += self::BATCH_SIZE;
        } while (count($resp->items) === $resp->limit);

        return $found;
    }

    protected function getImagesList(int $offset, int $limit): stdClass
    {
        $response = $this->client->request('GET', $this->baseUrl . "api/v1/images/?limit=$limit&offset=$offset");

        if ($response->getStatusCode() !== 200) {
            throw new UnexpectedValueException('API returned ' . $response->getStatusCode() . ' status code');
        }

        $listing = json_decode($response->getContent());
        usort($listing->items, function (object $a, object $b) {
            return strcmp($b->updated_at, $a->updated_at);
        });

        return $listing;
    }

    protected function getImageMetadata(string $name): ?stdClass
    {
        return $this->invokeaiCache->get('metadata-' . $name, function (ItemInterface $item) use ($name): \stdClass {
                    $item->expiresAfter(DateInterval::createFromDateString('1 month'));
                    $response = $this->client->request('GET', $this->baseUrl . "api/v1/images/$name/metadata");
                    $metadata = json_decode($response->getContent());

                    return $metadata;
                });
    }

    protected function searchPromptFor(string $name): array
    {
        $metadata = $this->getImageMetadata($name);

        if (!is_null($metadata->metadata)) {
            return explode(' ', $metadata->metadata->positive_prompt);
        }

        if (isset($metadata?->graph?->nodes?->esrgan)) {
            return $this->searchPromptFor($metadata->graph->nodes->esrgan->image->image_name);
        }

        return [];
    }

}
