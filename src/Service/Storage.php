<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Iterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function join_paths;

/**
 * Simple storage engine
 */
class Storage
{

    const tokenSize = 503;

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
        // struggling with cache
        clearstatcache(true, $path);
        $file->setEtag(sha1(filesize($path) . '-' . filemtime($path)));

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

    /**
     * Searches pictures by its filename (case insensitive or not)
     * Excludes token pictures and map pictures
     * @param string $title
     * @return Iterator
     */
    public function searchPictureByTitleContains(string $title): Iterator
    {
        $scan = new Finder();
        $scan->in($this->root)
                ->files()
                ->name("/$title.*\\.(jpg|jpeg|png|webp)$/i")
                ->notName('#^token-#');

        return $scan->getIterator();
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
     * @throws RuntimeException
     */
    public function delete(string $filename): void
    {
        $path = join_paths($this->root, $filename);

        if (!file_exists($path)) {
            throw new RuntimeException("Cannot find " . $filename);
        }

        $ret = unlink($path);
        if (!$ret) {
            throw new RuntimeException("Unable to delete " . $filename);
        }
    }

    public function getFileInfo(string $filename): SplFileInfo
    {
        return new SplFileInfo(join_paths($this->root, $filename));
    }

    /**
     * Store a uploaded picture into Storage as a JPEG
     * @param UploadedFile $picture
     * @param string $filename
     * @param int $maxDimension
     * @param int $compressionLevel
     * @throws RuntimeException
     */
    public function storePicture(UploadedFile $picture, string $filename, int $maxDimension = 1920, int $compressionLevel = 90): void
    {
        $targetName = join_paths($this->getRootDir(), $filename . '.jpg');
        if (file_exists($targetName)) {
            throw new RuntimeException("Picture $filename.jpg is already existing");
        }

        $source = imagecreatefromstring($picture->getContent());
        $dim = [imagesx($source), imagesy($source)];
        if (max($dim) > $maxDimension) {
            $ratio = (float) $maxDimension / max($dim);
            $width = (int) round($ratio * imagesx($source));
            $height = (int) round($ratio * imagesy($source));
            $target = imagescale($source, $width, $height);
        } else {
            $target = $source;
        }

        $ret = imagejpeg($target, $targetName, $compressionLevel);
        imagedestroy($source);
        imagedestroy($target);

        if (!$ret) {
            throw new RuntimeException("Unable to save $filename.jpg");
        }

        $check = imagecreatefromjpeg($targetName);
        if (false === $check) {
            throw new RuntimeException("$targetName is not readable or corrupted");
        }
        imagedestroy($check);
    }

    public function searchLastPicture(int $limit = 5): \Iterator
    {
        $iter = new Finder();

        return $iter->in($this->getRootDir())
                        ->name(['*.jpeg', '*.png', '*.jpg', '*.svg'])
                        ->notName('#^token-#')
                        ->sortByModifiedTime()
                        ->reverseSorting()
                        ->getIterator();
    }

    /**
     * Store a uploaded token into Storage as a PNG
     * @param UploadedFile $picture
     * @param string $filename
     * @throws RuntimeException
     */
    public function storeToken(UploadedFile $picture, string $filename): void
    {
        if ($picture->getMimeType() !== 'image/png') {
            throw new \InvalidArgumentException('Not a PNG format');
        }

        list($width, $height) = getimagesize($picture->getPathname());
        if ($width !== $height) {
            throw new RuntimeException('PNG image for token is not square');
        }

        $picture->move($this->getRootDir(), $filename);
    }

    /**
     * Search for broken picture in vertex content
     */
    public function searchForBrokenPicture(\Iterator $iter): array
    {
        $keep = [];
        // absolutely NOT optimized algorithm
        foreach ($iter as $vertex) {
            $scan = $vertex->extractPicture();
            foreach ($scan as $target) {
                $pic = $this->getFileInfo($target);
                if (!$pic->isReadable()) {
                    $keep[$target][$vertex->getTitle()] = true;
                }
            }
        }

        ksort($keep);

        return $keep;
    }

}
