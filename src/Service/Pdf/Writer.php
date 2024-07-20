<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Pdf;

use SplFileInfo;

/**
 * Contract for writing PDF
 */
interface Writer
{

    /**
     * Transforms a DOMDocument into PDF
     * @param \DOMDocument $doc
     * @param \SplFileInfo $target
     * @return void
     */
    public function domToPdf(\DOMDocument $doc, \SplFileInfo $target): void;

    /**
     * Transforms a template with param to PDF
     * @param string $template
     * @param array $param
     * @param SplFileInfo $target
     * @return void
     */
    public function renderToPdf(string $template, array $param, SplFileInfo $target): void;
}
