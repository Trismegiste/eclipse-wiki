<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use InvalidArgumentException;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
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

    public function warmUp(string $cacheDir): array
    {
        $this->fs->mkdir(join_paths($cacheDir, self::subDir));
        
        return [];
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function clear(string $cacheDir)
    {
        $this->fs->remove(join_paths($cacheDir, self::subDir));
    }

    public function get(string $url): BinaryFileResponse
    {
        return new BinaryFileResponse($this->download($url)->getPathname());
    }

    public function download(string $url): SplFileInfo
    {
        if (0 !== strpos($url, 'http')) {
            throw new InvalidArgumentException("$url is not a valid URL to a picture");
        }
        $filename = join_paths($this->cacheDir, sha1($url));

        if (!file_exists($filename)) {
            $resp = $this->client->request('GET', $url);
            file_put_contents($filename, $resp->getContent());
        }

        return new SplFileInfo($filename);
    }

}
