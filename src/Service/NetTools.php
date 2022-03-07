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
        socket_connect($sock, "8.8.8.8", 53);
        socket_getsockname($sock, $name); // $name passed by reference
        socket_close($sock);

        return $name;
    }

}
