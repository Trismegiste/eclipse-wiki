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
        $this->sendJsonEvent($channel, 'document', ['link' => $link, 'title' => $title]);
    }

    public function validPeering(int $identifier, string $title): void
    {
        $this->sendJsonEvent('peering', 'validation', ['identifier' => $identifier, 'characterTitle' => $title]);
    }

    public function pingRelativePosition(float $dx, float $dy): void
    {
        $this->sendJsonEvent('ping-position', 'relative', ['deltaX' => $dx, 'deltaY' => $dy]);
    }

    public function pingIndexedPosition(int $idx): void
    {
        $this->sendJsonEvent('ping-position', 'indexed', ['cell' => $idx]);
    }

    protected function sendJsonEvent(string $channel, string $type, array $content): void
    {
        $update = new Update(
                $channel,
                json_encode($content),
                type: $type
        );

        $this->hub->publish($update);
    }

}
