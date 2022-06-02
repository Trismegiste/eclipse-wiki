<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function join_paths;

/**
 * Cache for image from a MediaWiki
 */
class MwImageCache implements CacheWarmerInterface, CacheClearerInterface
{

    const subDir = 'mediawiki';

    protected $fs;
    protected $client;
    protected $cacheDir;

    public function __construct(Filesystem $fs, HttpClientInterface $client, string $cacheDir)
    {
        $this->fs = $fs;
        $this->client = $client;
        $this->cacheDir = join_paths($cacheDir, self::subDir);
    }

    public function warmUp(string $cacheDir)
    {
        $this->fs->mkdir(join_paths($cacheDir, self::subDir));
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function clear(string $cacheDir)
    {
        
    }

    public function get(string $url): BinaryFileResponse
    {
        $filename = join_paths($this->cacheDir, sha1($url) . ".webp");
        if (!file_exists($filename)) {
            $resp = $this->client->request('GET', $url);
            file_put_contents($filename, $resp->getContent());
        }

        return new BinaryFileResponse($filename);
    }

}
