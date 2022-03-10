<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Some tools for Networking
 */
class NetTools
{

    protected $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /** Gets the IP on the LAN */
    public function getLocalIp(): string
    {
        return $this->cache->get('my_ip_on_local_area_network', function (ItemInterface $item) {
                    $item->expiresAfter(\DateInterval::createFromDateString('5 minute'));
                    $name = '127.0.0.1';

                    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
                    if ($sock === false) {
                        throw new \RuntimeException("socket_create() failed. Reason: " . socket_strerror(socket_last_error()));
                    }

                    $ret = @socket_connect($sock, "8.8.8.8", 53);
                    if ($ret === false) {
                        throw new \RuntimeException("socket_connect() failed. Reason: " . socket_strerror(socket_last_error()));
                    }

                    socket_getsockname($sock, $name); // $name passed by reference
                    socket_close($sock);

                    return $name;
                });
    }

}
