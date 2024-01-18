<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Mercure;

use SplFileInfo;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * Server-Sent-Events
 */
class Pusher
{

    public function __construct(protected HubInterface $hub)
    {
        
    }

    public function sendPictureAsDataUrl(SplFileInfo $pic, string $eventType): void
    {
        $pictureInfo = getimagesize($pic->getPathname());

        $update = new Update(
                'public',
                'data:' . $pictureInfo['mime'] . ';base64,' . base64_encode(file_get_contents($pic->getPathname())),
                type: $eventType
        );

        $this->hub->publish($update);
    }

    public function sendDocumentLink(string $link, string $title, string $channel = 'public'): void
    {
        $update = new Update(
                $channel,
                json_encode(['link' => $link, 'title' => $title]),
                type: 'document'
        );

        $this->hub->publish($update);
    }

    public function askPeering(int $identifier): void
    {
        $update = new Update(
                'peering',
                json_encode(['identifier' => $identifier]),
                type: 'ask'
        );

        $this->hub->publish($update);
    }

    public function validPeering(int $identifier, string $npcTitle): void
    {
        $update = new Update(
                'peering',
                json_encode(['identifier' => $identifier, 'npcTitle' => $npcTitle]),
                type: 'validation'
        );

        $this->hub->publish($update);
    }

    public function pingRelativePosition(float $dx, float $dy): void
    {
        $update = new Update(
                'ping-position',
                json_encode(['deltaX' => $dx, 'deltaY' => $dy]),
                type: 'relative'
        );

        $this->hub->publish($update);
    }

    public function pingIndexedPosition(int $idx): void
    {
        $update = new Update(
                'ping-position',
                json_encode(['cell' => $idx]),
                type: 'indexed'
        );

        $this->hub->publish($update);
    }

}
