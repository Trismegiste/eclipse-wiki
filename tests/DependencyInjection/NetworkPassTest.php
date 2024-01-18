<?php

/*
 * eclipse-wiki
 */

class NetworkPassTest extends PHPUnit\Framework\TestCase
{

    public function testGetLocalIp()
    {
        $container = $this->createMock(Symfony\Component\DependencyInjection\ContainerBuilder::class);
        $container->expects($this->once())
                ->method('setParameter')
                ->with('network.local.ip');

        $sut = new \App\DependencyInjection\NetworkPass();
        $sut->process($container);
    }

}
