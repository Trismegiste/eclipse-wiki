<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Knp\Snappy\Pdf;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function join_paths;

/**
 * Generates and exposes PDF for public access
 */
class DocumentBroadcaster
{

    protected $knpPdf;
    protected $documentDir;

    public function __construct(protected UrlGeneratorInterface $routing, Pdf $knpPdf, string $cacheDir)
    {
        $this->knpPdf = $knpPdf;
        $this->documentDir = join_paths($cacheDir, PlayerCastCache::subDir);
    }

    public function generatePdf(string $fileTitle, string $htmlContent, array $options = []): SplFileInfo
    {
        $filename = $this->sanitizeFilename($fileTitle);
        $pathname = \join_paths($this->documentDir, $filename);
        $this->knpPdf->generateFromHtml($htmlContent, $pathname, $options, true);

        return new SplFileInfo($pathname);
    }

    protected function sanitizeFilename(string $filename): string
    {
        return preg_replace('#([^A-Za-z0-9-_\.])#', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $filename));
    }

    public function createResponseForFilename(string $filename): BinaryFileResponse
    {
        return new BinaryFileResponse(join_paths($this->documentDir, $filename));
    }

    public function getLinkToDocument(string $filename): string
    {
        return $this->routing->generate('app_playerlog_getdocument', ['filename' => $filename], UrlGeneratorInterface::ABSOLUTE_URL);
    }

}
