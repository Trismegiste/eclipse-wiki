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

    public function __construct(protected HubInterface $hub, protected array $pictureConfig)
    {
        
    }

    protected function slimPictureForPush(\GdImage $gd2, int $maxDimension): \GdImage
    {
        // checking dimension of picture
        $sx = imagesx($gd2);
        $sy = imagesy($gd2);
        $maxSize = max([$sx, $sy]);
        if ($maxSize > $maxDimension) {
            $forPlayer = imagescale($gd2, intval($sx * $maxDimension / $maxSize), intval($sy * $maxDimension / $maxSize));
            imagedestroy($gd2);
        } else {
            $forPlayer = $gd2;
        }

        return $forPlayer;
    }

    public function sendPictureAsDataUrl(GdImage $pic, string $eventType): void
    {
        $cfg = $this->pictureConfig[$eventType];
        $compressed = $this->slimPictureForPush($pic, $cfg['maxSize']);
        ob_start();
        imagejpeg($compressed, null, $cfg['quality']);
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
