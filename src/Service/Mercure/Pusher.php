<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Mercure;

use App\Service\SessionPushHistory;
use SplFileInfo;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * Server-Sent-Events
 */
class Pusher
{

    const filteredPicture = ['picture', 'profile'];

    public function __construct(protected HubInterface $hub, protected SessionPushHistory $store)
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

        if (in_array($eventType, self::filteredPicture)) {
            $this->store->backupFile($pic);
        }
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
