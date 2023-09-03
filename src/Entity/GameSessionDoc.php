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
    protected array $history = [];

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

    public function push(Vertex $vertex): void
    {
        if (!in_array($vertex->getCategory(), ['place', 'transhuman', 'scene', 'handout'])) {
            return;
        }

        $this->history[(string) $vertex->getPk()] = [
            'ts' => time(),
            'category' => $vertex->getCategory(),
            'title' => $vertex->getTitle()
        ];
    }

    public function getHistory(): array
    {
        $dump = $this->history;
        uasort($dump, function ($v1, $v2) {
            return $v2['ts'] <=> $v1['ts'];
        });

        return $dump;
    }

}
