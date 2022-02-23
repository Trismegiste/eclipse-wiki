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

    protected $macAddress;
    public $body;

    public function __construct(string $mac)
    {
        $this->macAddress = $mac;
    }

    public function getMacAddress(): string
    {
        return $this->macAddress;
    }

}
