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

    /**
     * Gets the timeline currently played ie pinned
     * @return array
     */
    public function getTimeline(): array
    {
        return [
            'pk' => $this->pinnedTimelinePk,
            'title' => $this->pinnedTimelineTitle
        ];
    }

    /**
     * Is a timeline currently pinned/player ?
     * @return bool
     */
    public function hasPinnedTimeline(): bool
    {
        return isset($this->pinnedTimelinePk);
    }

    /**
     * Stacks a vertex being shown
     * @param Vertex $vertex
     * @return void
     */
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

    /**
     * Gets the list of vertices browsed by the GM
     * @return array
     */
    public function getHistory(): array
    {
        $dump = $this->history;
        uasort($dump, function ($v1, $v2) {
            return $v2['ts'] <=> $v1['ts'];
        });

        return $dump;
    }

}
