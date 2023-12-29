<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Pdf;

use App\Service\MwImageCache;
use DOMDocument;
use DOMElement;
use SplFileInfo;
use Symfony\Component\Process\Process;
use Twig\Environment;
use function join_paths;
use function str_starts_with;

/**
 * PDF writer with headless Chromium
 */
class ChromiumPdfWriter implements Writer
{

    public function __construct(protected Environment $twig, protected string $cacheDir, protected MwImageCache $remoteImage)
    {
        
    }

    public function write(SplFileInfo $source, SplFileInfo $target): void
    {
        $chromium = new Process([
            'chromium',
            '--headless', '--disable-gpu', '--no-sandbox',
            '--print-to-pdf=' . $target->getPathname(),
            $source->getPathname()
        ]);
        $chromium->setTimeout(300);

        $chromium->mustRun();
    }

    public function renderToPdf(string $template, array $param, SplFileInfo $target): void
    {
        $source = new \SplFileInfo(join_paths($this->cacheDir, 'book-fandom-' . time() . '.html'));
        $content = $this->twig->render($template, $param);
        file_put_contents($source->getPathname(), $content);

        // redirect to localhost for downloading pictures (caching)
        $doc = new DOMDocument("1.0", "utf-8");
        libxml_use_internal_errors(true); // because HTML5 tags warning
        $doc->loadHTML($content);
        $errors = libxml_get_errors();
        $elements = $doc->getElementsByTagName('img');
        foreach ($elements as $img) {
            /** @var DOMElement $img */
            $src = $img->getAttribute('src');
            if (str_starts_with($src, 'http')) {
                $img->setAttribute('src', $this->remoteImage->getDataUri($src));
                $img->removeAttribute('srcset');
                $img->removeAttribute('loading');
                $img->removeAttribute('decoding');
                usleep(5e5);
            }
        }

        $local = new \SplFileInfo(join_paths($this->cacheDir, 'book-local-' . time() . '.html'));
        $doc->saveHTMLFile($local->getPathname());

        $this->write($local, $target);
    }

}
