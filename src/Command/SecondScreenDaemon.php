<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Launch the VNC server on the popup
 */
class SecondScreenDaemon extends Command
{

    protected static $defaultName = 'popup:vnc';

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("VNC Server for the Popup");
        $id = $this->getWindowIdentifier('PopupVNC - Mozilla Firefox');

        $io->info('Connect VNC to ' . $this->getLocalIp());
        $vnc = new Process(['x11vnc', '-viewonly', '-nopw', '-avahi', '-id', $id]);
        $vnc->setTimeout(null);
        $vnc->run(function ($type, $buffer)use ($io) {
            if (preg_match('#^PORT=(59[0-9]{2})#m', $buffer, $match)) {
                $io->info('On port = ' . $match[1]);
            }
        });

        return Command::SUCCESS;
    }

    protected function getWindowIdentifier(string $windowName): string
    {
        $scan = new Process(['xwininfo', '-name', $windowName]);
        try {
            $scan->mustRun();
            if (preg_match('#Window id: (0x[a-f0-9]+)\s#', $scan->getOutput(), $match)) {
                return $match[1];
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Popup window is not open');
        }
    }

    protected function getLocalIp(): string
    {
        $name = '127.0.0.1';
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_connect($sock, "8.8.8.8", 53);
        socket_getsockname($sock, $name); // $name passed by reference
        socket_close($sock);

        return $name;
    }

}
