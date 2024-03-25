<?php

/*
 * Eclipse Wiki
 */

namespace App\Service\StableDiffusion;

use DateInterval;
use stdClass;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

/**
 * Client for InvokeAI Stable Diffusion
 */
class InvokeAiClient extends PictureRepository
{

    const BATCH_SIZE = 100;
    const TIMEOUT = 2;

    public function __construct(protected HttpClientInterface $client, protected string $baseUrl, protected CacheInterface $invokeaiCache)
    {
        
    }

    /**
     * Searches for InvokeAI pictures
     * @param string $query a list of keywords that must all be used in the positive prompt
     * @param int $capFound
     * @return array
     */
    public function searchPicture(string $query, int $capFound = 10): array
    {
        $keywords = $this->splittingPrompt($query);
        $found = [];
        $offset = 0;

        do {
            $resp = $this->getImagesList($offset, self::BATCH_SIZE);
            foreach ($resp->items as $picture) {
                $prompt = $this->searchPromptFor($picture->image_name);
                if (!$this->matchKeywordAndPrompt($keywords, $prompt)) {
                    continue;
                }

                $found[] = new PictureInfo(
                        $this->baseUrl . $picture->image_url,
                        $this->baseUrl . $picture->thumbnail_url,
                        $picture->width,
                        $picture->image_name,
                        $prompt
                );
            }
            $offset += self::BATCH_SIZE;
        } while (count($resp->items) === $resp->limit);

        return $found;
    }

    /**
     * Gets a partial images list with offset and limit
     * @param int $offset
     * @param int $limit
     * @return stdClass
     * @throws UnexpectedValueException
     */
    protected function getImagesList(int $offset, int $limit): stdClass
    {
        return $this->invokeaiCache->get("picturelisting-$offset-$limit", function (ItemInterface $item) use ($limit, $offset): \stdClass {
                    $item->expiresAfter(DateInterval::createFromDateString('1 minute'));
                    $response = $this->client->request('GET', $this->baseUrl . "api/v1/images/?is_intermediate=false&categories=general&board_id=none&limit=$limit&offset=$offset", ['timeout' => self::TIMEOUT]);

                    if ($response->getStatusCode() !== 200) {
                        throw new UnexpectedValueException('API returned ' . $response->getStatusCode() . ' status code');
                    }

                    $listing = json_decode($response->getContent());
                    usort($listing->items, function (object $a, object $b) {
                        return strcmp($b->updated_at, $a->updated_at);
                    });

                    return $listing;
                });
    }

    /**
     * Gets the metadata for a given image and caches it
     * @param string $name
     * @return stdClass|null
     */
    protected function getImageMetadata(string $name): ?stdClass
    {
        return $this->invokeaiCache->get('metadata-' . $name, function (ItemInterface $item) use ($name): ?\stdClass {
                    $item->expiresAfter(DateInterval::createFromDateString('1 month'));
                    $response = $this->client->request('GET', $this->baseUrl . "api/v1/images/i/$name/metadata");
                    $metadata = json_decode($response->getContent());

                    return $metadata;
                });
    }

    public function getAbsoluteUrl(string $name): string
    {
        return $this->baseUrl . "api/v1/images/i/$name/full";
    }

    public function getThumbnailUrl(string $name): string
    {
        return $this->baseUrl . "api/v1/images/i/$name/thumbnail";
    }

    /**
     * Try to reach a prompt for an image
     * @param string $name
     * @return array
     */
    protected function searchPromptFor(string $name): string
    {
        $metadata = $this->getImageMetadata($name);

        // if there is metadata field, gets the positive prompt
        if (!is_null($metadata)) {
            return $metadata->positive_prompt;
        }

        // else, check if this is an embiggen image ?
        if (isset($metadata?->graph?->nodes?->esrgan)) {
            // Try to reach the prompt used for the original image
            return $this->searchPromptFor($metadata->graph->nodes->esrgan->image->image_name);
        }

        return '';
    }

    public function searchLastImage(int $limit = 27): iterable
    {
        $response = $this->client->request('GET', $this->baseUrl . "api/v1/images/?is_intermediate=false&categories=general&board_id=none&limit=$limit", ['timeout' => self::TIMEOUT]);
        if ($response->getStatusCode() !== 200) {
            throw new UnexpectedValueException('API returned ' . $response->getStatusCode() . ' status code');
        }

        $listing = json_decode($response->getContent());

        $found = [];
        foreach ($listing->items as $picture) {
            $found[] = new PictureInfo(
                    $this->baseUrl . $picture->image_url,
                    $this->baseUrl . $picture->thumbnail_url,
                    $picture->width,
                    $picture->image_name
            );
        }

        return $found;
    }

}
