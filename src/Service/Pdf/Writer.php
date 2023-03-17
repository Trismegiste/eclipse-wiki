<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Pdf;

/**
 * Contract for writing PDF
 */
interface Writer
{

    public function write(\SplFileInfo $source, \SplFileInfo $target): void;
}
