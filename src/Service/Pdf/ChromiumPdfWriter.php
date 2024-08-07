<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Pdf;

use App\Service\MwImageCache;
use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use SplFileInfo;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;
use function str_starts_with;

/**
 * PDF writer with headless Chromium
 */
class ChromiumPdfWriter implements Writer
{

    public function __construct(
            protected Environment $twig,
            protected MwImageCache $remoteImage,
            private HttpClientInterface $client,
            protected Stopwatch $stopwatch)
    {
        
    }

    public function domToPdf(\DOMDocument $doc, \SplFileInfo $target): void
    {
        $this->stopwatch->start('dom-to-pdf');
        $formFields = ['file' => new DataPart($doc->saveHTML(), 'doc-eclipsewiki.html', 'text/html')];
        $formData = new FormDataPart($formFields);

        $resp = $this->client->request('POST', "http://localhost:4444/upload", [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        file_put_contents($target->getPathname(), $resp->getContent());
        $this->stopwatch->stop('dom-to-pdf');
    }

    public function renderToPdf(string $template, array $param, SplFileInfo $target): void
    {
        $doc = $this->htmlToDom($this->twigToHtml($template, $param));
        $this->embedPictures($doc);
        $this->domToPdf($doc, $target);
    }

    public function twigToHtml(string $template, array $param): string
    {
        return $this->twig->render($template, $param);
    }

    public function htmlToDom(string $html): \DOMDocument
    {
        $doc = new DOMDocument("1.0", "utf-8");
        libxml_use_internal_errors(true); // because HTML5 tags warning
        $doc->loadHTML($html);
        $errors = libxml_get_errors();

        return $doc;
    }

    public function embedPictures(\DOMDocument $doc): void
    {
        $elements = $doc->getElementsByTagName('img');
        foreach ($elements as $img) {
            /** @var DOMElement $img */
            $src = $img->getAttribute('src');
            // remote
            if (str_starts_with($src, 'http')) {
                $img->setAttribute('src', $this->remoteImage->getDataUri($src));
                usleep(5e5);
            }
            // local
            if (str_starts_with($src, 'file:///')) {
                $img->setAttribute('src', $this->getDataUri($src));
            }
            $img->removeAttribute('srcset');
            $img->removeAttribute('loading');
            $img->removeAttribute('decoding');
        }
    }

    protected function getDataUri(string $localFile)
    {
        if (!preg_match('#^file://(/.+)#', $localFile, $match)) {
            throw new InvalidArgumentException("Invalid local URL $localFile");
        }
        $localFile = $match[1];
        if (!file_exists($localFile)) {
            throw new InvalidArgumentException("$localFile does not exist");
        }
        $pictureInfo = getimagesize($localFile);
        $mimetype = $pictureInfo['mime'];

        return "data:$mimetype;base64," . base64_encode(file_get_contents($localFile));
    }

}
