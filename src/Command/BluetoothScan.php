<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class BluetoothScan extends Command
{

    protected static $defaultName = 'bt:scan';
    protected static $defaultDescription = 'Scan for Bluetooth devices with OPUSH service';

    protected const xpath = '/record/attribute[@id="0x0001"]//uuid[@value="0x1105"]/parent::*/parent::*/following-sibling::attribute[@id="0x0004"]//uuid[@value="0x0003"]/following-sibling::uint8/@value';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $scan = new Process([
            'sdptool',
            'search', '--xml', 'OPUSH'
        ]);
        $scan->mustRun();
        $dump = $scan->getOutput();

        $oPushDevice = [];

        if (preg_match_all('#Searching for OPUSH on ([A-F0-9:]{17}).+(^<\?xml.+^</record>)#sm', $dump, $match, PREG_SET_ORDER, 0)) {
            foreach ($match as $device) {
                $doc = new \DOMDocument("1.0", "utf-8");
                $doc->loadXML($device[2]);
                $xpath = new \DOMXpath($doc);
                $elements = $xpath->query(self::xpath);

                $oPushDevice[] = [
                    'addr' => $device[1],
                    'name' => $this->getDeviceName($device[1]),
                    'channel' => (int) hexdec($elements->item(0)->nodeValue)
                ];
            }
        }

        if (count($oPushDevice)) {
            $io->success(count($oPushDevice) . ' devices found');
            $io->table(['addr', 'name', 'channel'], $oPushDevice);
        } else {
            $io->warning('No device found');
        }

        return Command::SUCCESS;
    }

    protected function getDeviceName(string $btAddr): string
    {
        $scan = new Process(['hcitool', 'name', $btAddr]);
        $scan->mustRun();
        $dump = $scan->getOutput();

        return trim($dump);
    }

}
