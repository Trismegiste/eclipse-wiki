<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Entity\BtMessage;
use App\Repository\BluetoothMsgRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * sdptool search --bdaddr FF:FF:B2:34:3C:58 OPUSH | grep "Channel"
 * https://doc.ubuntu-fr.org/bluetooth
 */
class BluetoothDaemon extends Command
{

    protected static $defaultName = 'bt:daemon';
    protected $repository;

    public function __construct(BluetoothMsgRepository $repo)
    {
        parent::__construct();
        $this->repository = $repo;
    }

    protected function configure(): void
    {
        $this->setDescription('Bluetooth daemon');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new \Symfony\Component\Console\Style\SymfonyStyle($input, $output);
        $io->title("Starting daemon");

        $this->repository->reset();
        $this->repository->save(new BtMessage('End', 0));
        $iterator = $this->repository->getTailableCursor();
        $iterator->next();

        while (true) {
            if ($iterator->valid()) {
                $document = $iterator->current();

                $output->writeln('Sending ' . $document->body . ' to ' . $document->btMac);
                $process = new Process(['obexftp',
                    '--nopath',
                    '--noconn',
                    '--uuid', 'none',
                    '--bluetooth', $document->btMac,
                    '--channel', $document->btChannel,
                    '--put', $document->body
                ]);
                $process->start();
            }

            $iterator->next();
        }

        return 0;
    }

}
