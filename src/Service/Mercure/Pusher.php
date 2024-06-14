<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Mercure;

use GdImage;
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

    public function sendPictureAsDataUrl(GdImage $pic, string $eventType): void
    {
        ob_start();
        imagejpeg($pic, null, 75);
        $content = ob_get_clean();

        $update = new Update(
                'public',
                'data:image/jpeg;base64,' . base64_encode($content),
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
