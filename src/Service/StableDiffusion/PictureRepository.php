<?php

/*
 * Eclipse Wiki
 */

namespace App\Service\StableDiffusion;

/**
 * Generic picture repository
 */
abstract class PictureRepository
{

    abstract public function searchPicture(string $query, int $capFound = 10): array;

    abstract public function getAbsoluteUrl(string $name): string;

    abstract public function getThumbnailUrl(string $name): string;

    protected function matchKeywordAndPrompt(array $keywords, string $prompt): bool
    {
        $splitted = $this->splittingPrompt($prompt);
        $filter = array_intersect($keywords, $splitted);

        return count($filter) === count($keywords);
    }

    protected function splittingPrompt(string $subject): array
    {
        $filtered = preg_replace('#[^a-z\s]#', ' ', strtolower($subject));

        return preg_split("/[\s]+/", $filtered, 0, PREG_SPLIT_NO_EMPTY);
    }

}
