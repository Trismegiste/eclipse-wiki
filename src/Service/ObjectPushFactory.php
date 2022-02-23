<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use App\Entity\BtMessage;
use App\Repository\BluetoothMsgRepository;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Create Bluetooth OPUSH message
 */
class ObjectPushFactory
{

    protected $deviceMac;
    protected $deviceChannel = null;
    protected $repository;

    public function __construct(string $mac, BluetoothMsgRepository $repo)
    {
        $this->deviceMac = $mac;
        $this->repository = $repo;
    }

    public function send(string $filename): void
    {
        if (is_null($this->deviceChannel)) {
            $this->deviceChannel = $this->getChannelFor($this->deviceMac); // should be in construct but slow
        }

        $msg = new BtMessage($this->deviceMac, $this->deviceChannel);
        $msg->body = $filename;
        $this->repository->save($msg);
    }

    protected function getChannelFor(string $btAddr): int
    {
        $scan = new Process([
            'sdptool',
            'search',
            '--bdaddr', $btAddr,
            'OPUSH'
        ]);
        $scan->mustRun();
        $dump = $scan->getOutput();
        if (!preg_match('#Channel:\s+(\d{1,2})#', $dump, $matches)) {
            throw new RuntimeException("Could not find the OPUSH channel for $btAddr");
        }

        return (int) $matches[1];
    }

}
