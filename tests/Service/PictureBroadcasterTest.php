<?php

/*
 * Eclipse Wiki
 */

use App\Service\PictureBroadcaster;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PictureBroadcasterTest extends WebTestCase
{

    protected PictureBroadcaster $sut;
    protected $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new PictureBroadcaster($this->logger, new SplFileInfo(join_paths(__DIR__, 'profilepic.png')));
    }

    public function testOpen()
    {
        $cnx = $this->createMock(ConnectionInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $request->expects($this->atLeastOnce())
                ->method('hasHeader')
                ->with('User-Agent')
                ->willReturn(true);

        $request->expects($this->atLeastOnce())
                ->method('getHeader')
                ->withConsecutive([$this->equalTo('User-Agent')], [$this->equalTo('X-Pusher')])
                ->willReturnOnConsecutiveCalls(['Mozilla'], []);
        $cnx->httpRequest = $request;

        $this->logger->expects($this->atLeastOnce())
                ->method('info')
                ->with($this->stringStartsWith('New connection'));

        $cnx->expects($this->atLeastOnce())
                ->method('send');

        $this->sut->onOpen($cnx);
    }

    public function testMessage()
    {
        // player connection
        $player = $this->createMock(ConnectionInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $request->expects($this->atLeastOnce())
                ->method('hasHeader')
                ->with('User-Agent')
                ->willReturn(true);

        $request->expects($this->atLeastOnce())
                ->method('getHeader')
                ->withConsecutive([$this->equalTo('User-Agent')], [$this->equalTo('X-Pusher')])
                ->willReturnOnConsecutiveCalls(['Mozilla'], []);

        $this->logger->expects($this->atLeastOnce())
                ->method('info')
                ->with($this->stringStartsWith('New connection'));

        $player->expects($this->exactly(2)) // for the onOpen and for the onMessage below
                ->method('send');

        $player->httpRequest = $request;
        $this->sut->onOpen($player);

        // gm push
        $cnx = $this->createMock(ConnectionInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $request->expects($this->atLeastOnce())
                ->method('hasHeader')
                ->with('User-Agent')
                ->willReturn(true);

        $request->expects($this->atLeastOnce())
                ->method('getHeader')
                ->withConsecutive([$this->equalTo('X-Pusher')], [$this->equalTo('User-Agent')])
                ->willReturnOnConsecutiveCalls(['Symfony'], ['Paragi']);
        $cnx->httpRequest = $request;

        $this->sut->onMessage($cnx, json_encode(['file' => __DIR__ . '/avatar.png']));
    }

    public function testNoMessageFromPlayer()
    {
        $cnx = $this->createMock(ConnectionInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $request->expects($this->atLeastOnce())
                ->method('getHeader')
                ->with('X-Pusher')
                ->willReturn([]);
        $cnx->httpRequest = $request;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('cannot send');
        $this->sut->onMessage($cnx, 'fail');
    }

    public function testDisconnect()
    {
        $conn = $this->createMock(ConnectionInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->atLeastOnce())
                ->method('hasHeader')
                ->with('User-Agent')
                ->willReturn(true);

        $request->expects($this->atLeastOnce())
                ->method('getHeader')
                ->withConsecutive([$this->equalTo('User-Agent')], [$this->equalTo('X-Pusher')])
                ->willReturnOnConsecutiveCalls(['Mozilla'], []);
        $conn->httpRequest = $request;

        $this->logger->expects($this->atLeastOnce())
                ->method('info');

        $this->sut->onClose($conn);
    }

    public function testError()
    {
        $conn = $this->createMock(ConnectionInterface::class);
        $conn->expects($this->once())
                ->method('close');
        $this->logger->expects($this->atLeastOnce())
                ->method('error');

        $this->sut->onError($conn, new \Exception());
    }

}
