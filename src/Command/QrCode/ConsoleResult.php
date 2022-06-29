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

    const blocky = "\xe2\x96\x88\xe2\x96\x88";

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
        for ($w = 0; $w < $this->matrix->getBlockCount() + 2; $w++) {
            $this->out->write(self::blocky);
        }
        $this->out->writeln('');

        for ($rowIndex = 0; $rowIndex < $this->matrix->getBlockCount(); ++$rowIndex) {
            $this->out->write(self::blocky);
            for ($columnIndex = 0; $columnIndex < $this->matrix->getBlockCount(); ++$columnIndex) {
                if (1 === $this->matrix->getBlockValue($rowIndex, $columnIndex)) {
                    $this->out->write('  ');
                } else {
                    $this->out->write(self::blocky);
                }
            }
            $this->out->writeln(self::blocky);
        }

        for ($w = 0; $w < $this->matrix->getBlockCount() + 2; $w++) {
            $this->out->write(self::blocky);
        }
        $this->out->writeln('');
    }

}
