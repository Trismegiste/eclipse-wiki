<?php

/*
 * eclipse-wiki
 */

use App\Service\PlayerFeedback;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;

class PlayerFeedbackTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->sut = new PlayerFeedback($logger);
    }

    public function testMessage()
    {
        $player = $this->createMock(ConnectionInterface::class);
        $player->expects($this->once()) // for the onOpen and for the onMessage below
                ->method('send');

        $this->sut->onOpen($player);
        $this->sut->onMessage($player, $this->createStub(MessageInterface::class));
        $this->sut->onClose($player);
    }

}
