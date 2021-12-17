<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Description of Bluetooth
 *
 * @author flo
 */
class Bluetooth extends Command
{

    protected static $defaultName = 'app:bt';

    protected function configure(): void
    {
        $this
                ->addArgument('addr', InputArgument::REQUIRED)
                ->addArgument('file', InputArgument::REQUIRED)
                ->setDescription('Bluetooth');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $process = new Process(['obexftp',
            '--nopath',
            '--noconn',
            '--uuid', 'none',
            '--bluetooth', $input->getArgument('addr'),
            '--channel', 12,
            '--put', $input->getArgument('file')]);
        $process->run();

        // sdptool search --bdaddr 90:78:B2:34:3C:58 OPUSH | grep "Channel"

        return 0;
    }

}
