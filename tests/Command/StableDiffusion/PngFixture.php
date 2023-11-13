<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Command\StableDiffusion;

use App\Tests\Service\StableDiffusion\PngReaderTest;

/**
 * Insert PNG pictures from InvokeAI
 */
trait PngFixture
{

    protected function insertFixturesInto(string $storageFolder)
    {
        $folder = __DIR__ . '/../../fixtures';
        $src = join_paths($folder, PngReaderTest::fixture);
        $dst = join_paths($storageFolder, PngReaderTest::fixture);
        copy($src, $dst);
    }

    protected function deleteFixturesInto(string $storageFolder)
    {
        $dst = join_paths($storageFolder, PngReaderTest::fixture);
        @unlink($dst);
    }

}
