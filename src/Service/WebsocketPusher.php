<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Paragi\PhpWebsocket\Client;
use Psr\Log\LoggerInterface;
use Ratchet\App;
use SplFileInfo;
use function join_paths;

/**
 * A websocket client that pushes messages to Websocket server
 * (lazy connection for services working environment)
 */
class WebsocketPusher
{

    const ROUTE_PICTURE = '/picture';
    const ROUTE_CUBEMAP = '/cubemap';
    const ROUTE_FEEDBACK = '/feedback';

    protected $localIp;
    protected $wsPort;
    protected $logger;
    protected string $publicDir;

    public function __construct(NetTools $nettools, LoggerInterface $websoxLogger, int $websocketPort, string $publicFolder)
    {
        $this->localIp = $nettools->getLocalIp();
        $this->wsPort = $websocketPort;
        $this->logger = $websoxLogger;
        $this->publicDir = $publicFolder;
    }

    public function getUrlPicture(): string
    {
        return 'ws://' . $this->localIp . ':' . $this->wsPort . self::ROUTE_PICTURE;
    }

    public function getUrlCubemap(): string
    {
        return 'ws://' . $this->localIp . ':' . $this->wsPort . self::ROUTE_CUBEMAP;
    }

    public function getUrlFeedback(): string
    {
        return 'ws://' . $this->localIp . ':' . $this->wsPort . self::ROUTE_FEEDBACK;
    }

    protected function createDefault(string $pic): \SplFileInfo
    {
        return new SplFileInfo(join_paths($this->publicDir, 'img', $pic));
    }

    public function createServer(): App
    {
        $app = new App($this->localIp, $this->wsPort, '0.0.0.0');
        $app->route(self::ROUTE_PICTURE, new PictureBroadcaster($this->logger, $this->createDefault('mire.png')), ['*']);
        $app->route(self::ROUTE_CUBEMAP, new PictureBroadcaster($this->logger, $this->createDefault('cubemap.png')), ['*']);
        $app->route(self::ROUTE_FEEDBACK, new PlayerFeedback($this->logger), ['*']);

        return $app;
    }

    public function push(string $data, string $imgType): string
    {
        $route = ['2d' => self::ROUTE_PICTURE, '3d' => self::ROUTE_CUBEMAP][$imgType];
        $sp = new Client($this->localIp, $this->wsPort, ['X-Pusher: Symfony'], $err, 10, false, false, $route);
        $sp->write($data);
        $reading = $sp->read();
        $this->logger->debug("Server responded with: $reading");

        return $reading;
    }

}
