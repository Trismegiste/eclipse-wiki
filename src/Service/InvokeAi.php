<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use stdClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

/**
 * Client for InvokeAI Stable Diffusion
 */
class InvokeAi
{

    const BATCH_SIZE = 100;

    public function __construct(protected HttpClientInterface $client, protected string $baseUrl)
    {
        
    }

    public function searchPicture(string $query, int $capFound = 10): array
    {
        $keywords = explode(' ', $query);
        $found = [];
        $offset = 0;

        do {
            $resp = $this->getImagesList($offset, self::BATCH_SIZE);
            foreach ($resp->items as $picture) {
                $metadata = $this->getImageMetadata($picture->image_name);
                if (is_null($metadata)) {
                    continue;
                }
                foreach ($keywords as $keyword) {
                    if (!str_contains($metadata->positive_prompt, $keyword)) {
                        continue 2;
                    }
                }
                $found[] = (object) ['full' => $this->baseUrl . $picture->image_url, 'thumb' => $this->baseUrl . $picture->thumbnail_url];
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

        return json_decode($response->getContent());
    }

    protected function getImageMetadata(string $name): ?stdClass
    {
        $response = $this->client->request('GET', $this->baseUrl . "api/v1/images/$name/metadata");
        $metadata = json_decode($response->getContent());

        return $metadata->metadata;
    }

}
