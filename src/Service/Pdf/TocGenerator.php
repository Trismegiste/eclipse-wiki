<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Pdf;

use SplFileInfo;
use Symfony\Component\Process\Process;

/**
 * Extracts, edits, injects a new TOC into an existing PDF
 */
class TocGenerator
{

    public function extractMeta(SplFileInfo $source, int $page, int $level, string $titleStartWith, string $prepend = ''): string
    {
        $tocgen = new Process([
            'pdfxmeta',
            '-p', $page,
            '-a', $level,
            $source->getPathname(),
            "^$titleStartWith"
        ]);

        return $prepend . $tocgen->mustRun()->getOutput();
    }

    public function generateToc(SplFileInfo $source, string $receipe): string
    {
        $tocgen = new Process([
            'pdftocgen',
            $source->getPathname()
        ]);
        $tocgen->setInput($receipe);

        return $tocgen->mustRun()->getOutput();
    }

    public function injectToc(SplFileInfo $source, SplFileInfo $target, string $toc): void
    {
        $tocgen = new Process([
            'pdftocio',
            $source->getPathname(),
            '-o', $target->getPathname()
        ]);
        $tocgen->setInput($toc);

        $tocgen->mustRun();
    }

}
