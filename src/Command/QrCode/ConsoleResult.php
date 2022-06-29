<?php

/*
 * eclipse-wiki
 */

namespace App\Command\QrCode;

use Endroid\QrCode\Matrix\MatrixInterface;
use Endroid\QrCode\Writer\Result\AbstractResult;

/**
 * Print QR Code for CLI
 */
class ConsoleResult extends AbstractResult
{

    const TWOBLOCKS = [
        0 => "\xe2\x96\x88",
        1 => "\xe2\x96\x84",
        2 => "\xe2\x96\x80",
        3 => ' '
    ];

    protected $matrix;
    protected $twoblocks;

    public function __construct(MatrixInterface $matrix, bool $inverted)
    {
        $this->matrix = $matrix;
        $this->twoblocks = $inverted ? array_reverse(self::TWOBLOCKS) : self::TWOBLOCKS;
    }

    public function getMimeType(): string
    {
        return 'text/plain';
    }

    public function getString(): string
    {
        $side = $this->matrix->getBlockCount();

        ob_start();
        echo str_repeat($this->twoblocks[0], $side + 4) . PHP_EOL;

        for ($rowIndex = 0; $rowIndex < $side; $rowIndex += 2) {
            echo $this->twoblocks[0] . $this->twoblocks[0];
            for ($columnIndex = 0; $columnIndex < $side; $columnIndex++) {
                $combined = $this->matrix->getBlockValue($rowIndex, $columnIndex);
                if (($rowIndex + 1) < $side) {
                    $combined |= $this->matrix->getBlockValue($rowIndex + 1, $columnIndex) << 1;
                }
                echo $this->twoblocks[$combined];
            }
            echo $this->twoblocks[0] . $this->twoblocks[0] . PHP_EOL;
        }

        echo str_repeat($this->twoblocks[0], $side + 4) . PHP_EOL;

        return ob_get_clean();
    }

}
