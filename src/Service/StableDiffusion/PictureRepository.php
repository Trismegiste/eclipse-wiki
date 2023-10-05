<?php

/*
 * Eclipse Wiki
 */

namespace App\Service\StableDiffusion;

/**
 *
 * @author florent
 */
interface PictureRepository
{

    public function searchPicture(string $query, int $capFound = 10): array;

    public function getAbsoluteUrl(string $name): string;

    public function getThumbnailUrl(string $name): string;
}
