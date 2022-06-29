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

    const twoblocks = [
        0 => "\xe2\x96\x88",
        1 => "\xe2\x96\x84",
        2 => "\xe2\x96\x80",
        3 => ' '
    ];

    protected $matrix;

    public function __construct(MatrixInterface $matrix)
    {
        $this->matrix = $matrix;
    }

    public function getMimeType(): string
    {
        return 'text/plain';
    }

    public function getString(): string
    {
        $side = $this->matrix->getBlockCount();

        ob_start();
        echo str_repeat(self::twoblocks[0], $side + 4) . PHP_EOL;

        for ($rowIndex = 0; $rowIndex < $side; $rowIndex += 2) {
            echo self::twoblocks[0] . self::twoblocks[0];
            for ($columnIndex = 0; $columnIndex < $side; ++$columnIndex) {
                $combined = $this->matrix->getBlockValue($rowIndex, $columnIndex);
                if (($rowIndex + 1) < $side) {
                    $combined |= $this->matrix->getBlockValue($rowIndex + 1, $columnIndex) << 1;
                }
                echo self::twoblocks[$combined];
            }
            echo self::twoblocks[0] . self::twoblocks[0] . PHP_EOL;
        }

        echo str_repeat(self::twoblocks[0], $side + 4) . PHP_EOL;

        return ob_get_clean();
    }

}
