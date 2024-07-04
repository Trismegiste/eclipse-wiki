<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use App\Service\Pdf\ChromiumPdfWriter;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Generates and exposes PDF for public access
 */
class DocumentBroadcaster
{

    public function __construct(protected UrlGeneratorInterface $routing,
            protected PlayerCastCache $playerCache,
            protected ChromiumPdfWriter $chromiumPdf)
    {
        
    }

    public function generatePdf(string $fileTitle, string $htmlContent): SplFileInfo
    {
        $target = $this->playerCache->createTargetFile($fileTitle);

        $doc = $this->chromiumPdf->htmlToDom($htmlContent);
        $this->chromiumPdf->embedPictures($doc);
        $this->chromiumPdf->domToPdf($doc, $target);

        return $target;
    }

    public function createResponseForFilename(string $filename): BinaryFileResponse
    {
        return $this->playerCache->createResponse($filename);
    }

    public function getLinkToDocument(string $filename): string
    {
        return $this->routing->generate('app_playerlog_getdocument', ['filename' => $filename], UrlGeneratorInterface::ABSOLUTE_URL);
    }

}
