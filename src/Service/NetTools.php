<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

/**
 * Some tools for Networking
 */
class NetTools
{

    /** Gets the IP on the LAN */
    public function getLocalIp(): string
    {
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
    }

}
