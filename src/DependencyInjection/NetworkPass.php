<?php

/*
 * eclipse-wiki
 */

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Param injection for local networking
 */
class NetworkPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container): void
    {
        $ip = $this->getLocalIp();
        $container->setParameter('network.local.ip', $ip);
        $def = $container->getDefinition('mercure.hub.default');
        $def->setArgument('$url', "http://$ip/.well-known/mercure");
        $def->setArgument('$publicUrl', "http://$ip/.well-known/mercure");
    }

    protected function getLocalIp(): string
    {
        $name = '127.0.0.1';
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock === false) {
            throw new RuntimeException("socket_create() failed. Reason: " . socket_strerror(socket_last_error()));
        }

        $ret = @socket_connect($sock, "8.8.8.8", 53);
        if ($ret === false) {
            throw new RuntimeException("socket_connect() failed. Reason: " . socket_strerror(socket_last_error()));
        }

        socket_getsockname($sock, $name); // $name passed by reference
        socket_close($sock);

        return $name;
    }

}
