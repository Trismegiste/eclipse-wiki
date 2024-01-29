<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use RuntimeException;
use SplFileInfo;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client to API https://www.file.io/developers
 */
class FileIoClient
{

    public function __construct(protected HttpClientInterface $client)
    {
        
    }

    public function upload(SplFileInfo $source): string
    {
        $handle = fopen($source->getPathname(), 'r');
        $response = $this->client->request('POST', 'https://file.io', ['body' => [
                'file' => $handle,
                'expires' => '1h',
                'maxDownloads' => 1,
                'autoDelete' => true
        ]]);

        $ret = json_decode($response->getContent());
        fclose($handle);

        if (!$ret->success || ($ret->status !== 200)) {
            throw new RuntimeException('Upload failed with status code ' . $ret->status);
        }

        return $ret->link;
    }

}
