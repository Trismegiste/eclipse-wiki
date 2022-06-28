<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

/**
 * Images for tests
 */
trait PictureFixture
{

    protected function createTestChart(int $side)
    {
        $target = imagecreatetruecolor($side, $side);
        $bg = imagecolorallocate($target, 0xff, 0xff, 0xff);
        imagefill($target, 0, 0, $bg);

        $fg = imagecolorallocate($target, 0xff, 0, 0);
        imagefilledellipse($target, $side / 2, $side / 2, $side / 2, $side / 2, $fg);

        return $target;
    }

}
