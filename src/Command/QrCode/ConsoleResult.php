<?php

/*
 * eclipse-wiki
 */

namespace App\Command\QrCode;

use Endroid\QrCode\Matrix\MatrixInterface;
use Endroid\QrCode\Writer\Result\AbstractResult;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Print QR Code to symfony Console
 */
class ConsoleResult extends AbstractResult
{

    protected $out;
    protected $matrix;

    public function __construct(OutputInterface $out, MatrixInterface $matrix)
    {
        $this->out = $out;
        $this->matrix = $matrix;
    }

    public function getMimeType(): string
    {
        
    }

    public function getString(): string
    {
        
    }

    public function dump()
    {
        for ($rowIndex = 0; $rowIndex < $this->matrix->getBlockCount(); ++$rowIndex) {
            for ($columnIndex = 0; $columnIndex < $this->matrix->getBlockCount(); ++$columnIndex) {
                if (1 === $this->matrix->getBlockValue($rowIndex, $columnIndex)) {
                    $this->out->write('  ');
                } else {
                    $this->out->write("\xe2\x96\x88\xe2\x96\x88");
                }
            }
            $this->out->writeln('');
        }
    }

}
