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
 * Description of DocumentBroadcaster
 */
class DocumentBroadcaster
{

    protected $netTools;
    protected $knpPdf;
    protected $documentDir;
    protected $urlGenerator;

    public function __construct(NetTools $ntools, Pdf $knpPdf, UrlGeneratorInterface $urlGen, string $cacheDir)
    {
        $this->netTools = $ntools;
        $this->knpPdf = $knpPdf;
        $this->documentDir = join_paths($cacheDir, PlayerCastCache::subDir);
        $this->urlGenerator = $urlGen;
    }

    public function getExternalLinkForGeneratedPdf(string $fileTitle, string $htmlContent, array $options = []): string
    {
        $filename = $this->sanitizeFilename($fileTitle);
        $pathname = \join_paths($this->documentDir, $filename);

        $this->knpPdf->generateFromHtml($htmlContent, $pathname, $options, true);
        $url = $this->urlGenerator->generate('app_playercast_getdocument', ['filename' => $filename], UrlGeneratorInterface::ABSOLUTE_URL);
        $lan = preg_replace('#//localhost#', '//' . $this->netTools->getLocalIp(), $url); // @todo hardcoded config

        return $lan;
    }

    protected function sanitizeFilename(string $filename): string
    {
        return str_replace(' ', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $filename));
    }

    public function createResponseForFilename(string $filename): BinaryFileResponse
    {
        return new BinaryFileResponse(join_paths($this->documentDir, $filename));
    }

}
