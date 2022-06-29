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

    const twoblocks = [
        0 => "\xe2\x96\x88",
        1 => "\xe2\x96\x84",
        2 => "\xe2\x96\x80",
        3 => ' '
    ];

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
        $side = $this->matrix->getBlockCount();

        for ($w = 0; $w < $side + 2; $w++) {
            $this->out->write(self::twoblocks[0]);
        }
        $this->out->writeln('');

        for ($rowIndex = 0; $rowIndex < $side; $rowIndex += 2) {
            $this->out->write(self::twoblocks[0]);
            for ($columnIndex = 0; $columnIndex < $side; ++$columnIndex) {
                $combined = $this->matrix->getBlockValue($rowIndex, $columnIndex);
                if (($rowIndex + 1) < $side) {
                    $combined += $this->matrix->getBlockValue($rowIndex + 1, $columnIndex) << 1;
                }
                $this->out->write(self::twoblocks[$combined]);
            }
            $this->out->writeln(self::twoblocks[0]);
        }

        for ($w = 0; $w < $side + 2; $w++) {
            $this->out->write(self::twoblocks[0]);
        }
        $this->out->writeln('');
    }

}
