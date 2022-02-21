<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Iterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function join_paths;

/**
 * Storage
 */
class Storage
{

    protected $root;

    public function __construct(string $projectDir, string $env)
    {
        $this->root = join_paths($projectDir, '/var/storage/', $env);
    }

    public function getRootDir(): string
    {
        return $this->root;
    }

    public function createResponse(string $filename): BinaryFileResponse
    {
        $path = join_paths($this->root, $filename);
        if (!file_exists($path)) {
            throw new NotFoundHttpException($filename . ' does not exist');
        }

        return new BinaryFileResponse($path);
    }

    public function searchByTitleContains(string $title, bool $caseInsensitive = true): Iterator
    {
        return $this->searchByName("/$title/" . ($caseInsensitive ? 'i' : ''));
    }

    public function searchByName(string $glob): \Iterator
    {
        $scan = new Finder();
        $scan->in($this->root)
                ->files()
                ->name($glob);

        return $scan->getIterator();
    }

    public function delete(string $filename): void
    {
        $path = join_paths($this->root, $filename);

        if (!file_exists($path)) {
            throw new \RuntimeException("Cannot find " . $filename);
        }

        $ret = unlink($path);
        if (!$ret) {
            throw new \RuntimeException("Unable to delete " . $filename);
        }
    }

}
