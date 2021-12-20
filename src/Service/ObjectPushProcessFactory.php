<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Symfony\Component\Process\Process;

/**
 * Create Process for OPUSH bluetooth service
 */
class ObjectPushProcessFactory
{

    protected $deviceMac;
    protected $deviceChannel;

    public function __construct(string $mac, int $chan)
    {
        $this->deviceChannel = $chan;
        $this->deviceMac = $mac;
    }

    public function create(string $filename): Process
    {
        return new Process(['obexftp',
            '--nopath',
            '--noconn',
            '--uuid', 'none',
            '--bluetooth', $this->deviceMac,
            '--channel', $this->deviceChannel,
            '--put', $filename
        ]);
    }

}
