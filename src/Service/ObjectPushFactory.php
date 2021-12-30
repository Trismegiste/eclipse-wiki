<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use App\Entity\BtMessage;
use App\Repository\BluetoothMsgRepository;

/**
 * Create Bluetooth OPUSH message
 */
class ObjectPushFactory
{

    protected $deviceMac;
    protected $deviceChannel;
    protected $repository;

    public function __construct(string $mac, int $chan, BluetoothMsgRepository $repo)
    {
        $this->deviceChannel = $chan;
        $this->deviceMac = $mac;
        $this->repository = $repo;
    }

    public function send(string $filename): void
    {
        $msg = new BtMessage($this->deviceMac, $this->deviceChannel);
        $msg->body = $filename;
        $this->repository->save($msg);
    }

}
