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

}
