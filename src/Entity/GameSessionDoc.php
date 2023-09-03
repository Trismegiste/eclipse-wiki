<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * The current game session document
 */
class GameSessionDoc
{

    protected \MongoDB\BSON\ObjectId $pinnedTimelinePk;
    protected string $pinnedTimelineTitle;

    public function setTimeline(Timeline $current): void
    {
        // only duplicate relevant information
        $this->pinnedTimelinePk = $current->getPk();
        $this->pinnedTimelineTitle = $current->getTitle();
    }

    public function getTimeline(): array
    {
        return [
            'pk' => $this->pinnedTimelinePk,
            'title' => $this->pinnedTimelineTitle
        ];
    }

    public function hasPinnedTimeline(): bool
    {
        return isset($this->pinnedTimelinePk);
    }

}
