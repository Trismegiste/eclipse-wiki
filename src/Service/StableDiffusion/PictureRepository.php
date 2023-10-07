<?php

/*
 * Eclipse Wiki
 */

namespace App\Service\StableDiffusion;

/**
 * Generic picture repository
 */
interface PictureRepository
{

    public function searchPicture(string $query, int $capFound = 10): array;

    public function getAbsoluteUrl(string $name): string;

    public function getThumbnailUrl(string $name): string;
}
