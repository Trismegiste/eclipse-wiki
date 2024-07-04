<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use InvalidArgumentException;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function str_starts_with;

/**
 * Cache for image from a MediaWiki
 */
class MwImageCache extends LocalFileCache
{

    public function __construct(Filesystem $fs, string $folder, protected HttpClientInterface $client)
    {
        parent::__construct($fs, $folder);
    }

    public function get(string $url): BinaryFileResponse
    {
        return $this->createResponse($this->download($url)->getBasename());
    }

    public function download(string $url): SplFileInfo
    {
        if (0 !== strpos($url, 'http')) {
            throw new InvalidArgumentException("$url is not a valid URL to a picture");
        }
        $filename = $this->createTargetFile(sha1($url));

        if (!file_exists($filename)) {
            $resp = $this->client->request('GET', $url);
            file_put_contents($filename, $resp->getContent());
        }

        return new SplFileInfo($filename);
    }

    public function getDataUri(string $url): string
    {
        if (!str_starts_with($url, 'http')) {
            throw new InvalidArgumentException("$url is not a valid URL to a picture");
        }
        $resp = $this->client->request('GET', $url);
        $mimetype = $resp->getHeaders()['content-type'][0];

        return "data:$mimetype;base64," . base64_encode($resp->getContent());
    }

}
