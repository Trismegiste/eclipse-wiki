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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Writer of QR Code to Symfony Console
 */
class ConsoleWriter implements WriterInterface
{

    protected $out;

    public function __construct(OutputInterface $out)
    {
        $this->out = $out;
    }

    public function write(QrCodeInterface $qrCode, LogoInterface $logo = null, LabelInterface $label = null, $options = []): ResultInterface
    {
        $matrixFactory = new MatrixFactory();
        $matrix = $matrixFactory->create($qrCode);

        return new ConsoleResult($this->out, $matrix);
    }

}
