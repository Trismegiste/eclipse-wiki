<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Pdf;

use SplFileInfo;
use Symfony\Component\Process\Process;

/**
 * PDF writer with headless Chromium
 */
class ChromiumPdfWriter implements Writer
{

    protected \Twig\Environment $twig;
    protected $cacheDir;

    public function __construct(\Twig\Environment $twig, string $cacheDir)
    {
        $this->twig = $twig;
        $this->cacheDir = $cacheDir;
    }

    public function write(SplFileInfo $source, SplFileInfo $target): void
    {
        $chromium = new Process([
            'chromium',
            '--headless', '--disable-gpu', '--no-sandbox',
            '--print-to-pdf=' . $target->getPathname(),
            $source->getPathname()
        ]);

        $chromium->mustRun();
    }

    public function renderToPdf(string $template, array $param, SplFileInfo $target): void
    {
        $content = $this->twig->render($template, $param);
        $source = new \SplFileInfo(join_paths($this->cacheDir, 'book-' . time() . '.html'));
        file_put_contents($source->getPathname(), $content);

        $this->write($source, $target);
    }

}
