<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Timeline of events
 */
class Timeline extends Vertex
{

    public function getEvent(): array
    {
        
    }

    public function setEvent(array $listing): void
    {
        ob_start();
        foreach ($listing as $item) {
            /** @var Event $item */
            echo '*';

            if ($item->completed) {
                echo '<s>';
            }

            echo $item->pitch;

            if ($item->completed) {
                echo '</s>';
            }

            echo PHP_EOL;
        }

        $this->content = ob_get_clean();
    }

}
