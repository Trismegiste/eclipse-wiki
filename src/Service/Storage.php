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
 * Simple storage engine
 */
class Storage
{

    protected $root;

    public function __construct(string $projectDir, string $env)
    {
        $this->root = join_paths($projectDir, '/var/storage/', $env);
    }

    /**
     * The root directory where files are stored
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->root;
    }

    /**
     * Create a binary response for a given filename stored on the local disk
     * @param string $filename
     * @return BinaryFileResponse
     * @throws NotFoundHttpException
     */
    public function createResponse(string $filename): BinaryFileResponse
    {
        $path = join_paths($this->root, $filename);
        if (!file_exists($path)) {
            throw new NotFoundHttpException($filename . ' does not exist');
        }

        $file = new BinaryFileResponse($path);
        $file->setAutoEtag();
        $file->setAutoLastModified();

        return $file;
    }

    /**
     * Searches files by its filename (case insensitive or not)
     * @param string $title
     * @param bool $caseInsensitive
     * @return Iterator
     */
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

    /**
     * Deletes one file on the storage
     * @param string $filename
     * @throws \RuntimeException
     */
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
