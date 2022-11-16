<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function join_paths;

/**
 * Generates and exposes PDF for public access
 */
class DocumentBroadcaster
{

    protected $netTools;
    protected $knpPdf;
    protected $documentDir;

    public function __construct(NetTools $ntools, Pdf $knpPdf, string $cacheDir)
    {
        $this->netTools = $ntools;
        $this->knpPdf = $knpPdf;
        $this->documentDir = join_paths($cacheDir, PlayerCastCache::subDir);
    }

    public function getExternalLinkForGeneratedPdf(string $fileTitle, string $htmlContent, array $options = []): string
    {
        $filename = $this->sanitizeFilename($fileTitle);
        $pathname = \join_paths($this->documentDir, $filename);
        $this->knpPdf->generateFromHtml($htmlContent, $pathname, $options, true);

        return $this->netTools->generateUrlForExternalAccess('app_playercast_getdocument', ['filename' => $filename]);
    }

    protected function sanitizeFilename(string $filename): string
    {
        return preg_replace('#([^A-Za-z0-9-_\.])#', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $filename));
    }

    public function createResponseForFilename(string $filename): BinaryFileResponse
    {
        return new BinaryFileResponse(join_paths($this->documentDir, $filename));
    }

}
