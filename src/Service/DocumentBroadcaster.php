<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use App\Service\Pdf\ChromiumPdfWriter;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tekkcraft\EpubGenerator\EpubDocument;
use Tekkcraft\EpubGenerator\EpubSection;

/**
 * Generates and exposes PDF & ePub for public access
 */
class DocumentBroadcaster
{

    public function __construct(protected UrlGeneratorInterface $routing,
            protected PlayerCastCache $playerCache,
            protected ChromiumPdfWriter $chromiumPdf,
            protected TranslatorInterface $trans)
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

    public function generateScenarioEpub(array $scenario, string $author): SplFileInfo
    {
        $generator = new EpubDocument($scenario['title'], $author, $scenario['title'], $this->playerCache->getFolder());

        $section = ['pitch', 'story', 'act1', 'act2', 'act3', 'act4', 'act5'];
        foreach ($section as $key) {
            $title = $this->trans->trans('DT_' . strtoupper($key));
            $content = new EpubSection(
                    $title,
                    $title,
                    "<h2>$title</h2><p>" . nl2br($scenario[$key]) . '</p>',
            );
            $generator->addSection($content);
        }

        $epubFile = $generator->generateEpub();

        return new SplFileInfo($epubFile);
    }

}
