<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * Message fo Bluetooth
 */
class BtMessage implements \Trismegiste\Toolbox\MongoDb\Root
{

    use \Trismegiste\Toolbox\MongoDb\RootImpl;

    public $btMac;
    public $btChannel;
    public $body;

    public function __construct(string $mac, int $chan)
    {
        $this->btChannel = $chan;
        $this->btMac = $mac;
    }

}
