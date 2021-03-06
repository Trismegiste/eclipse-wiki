<?php

/*
 * eclipse-wiki
 */

namespace App\Command\QrCode;

use Endroid\QrCode\Bacon\MatrixFactory;
use Endroid\QrCode\Label\LabelInterface;
use Endroid\QrCode\Logo\LogoInterface;
use Endroid\QrCode\QrCodeInterface;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\WriterInterface;

/**
 * Writer of QR Code for CLI
 */
class ConsoleWriter implements WriterInterface
{

    protected $inverted;

    public function __construct(bool $inverted = false)
    {
        $this->inverted = $inverted;
    }

    public function write(QrCodeInterface $qrCode, LogoInterface $logo = null, LabelInterface $label = null, $options = []): ResultInterface
    {
        $matrixFactory = new MatrixFactory();
        $matrix = $matrixFactory->create($qrCode);

        return new ConsoleResult($matrix, $this->inverted);
    }

}
