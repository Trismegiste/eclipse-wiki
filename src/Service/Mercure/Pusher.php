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

}
