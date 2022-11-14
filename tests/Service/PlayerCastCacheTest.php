<?php

/*
 * eclipse-wiki
 */

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\PlayerCastCache;

class PlayerCastCacheTest extends KernelTestCase
{

    protected $sut;

    public function setUp(): void
    {
        $this->sut = static::getContainer()->get(PlayerCastCache::class);
    }

    public function getSizeConfig()
    {
        return [[700], [1100]];
    }

    /** @dataProvider getSizeConfig */
    public function testSlimPicture(int $side)
    {
        $big = imagecreatetruecolor($side, $side);
        $target = sys_get_temp_dir() . '/big.png';

        $white = imagecolorallocate($big, 255, 255, 255);
        for ($x = 0; $x < $side; $x++) {
            for ($y = 0; $y < $side; $y++) {
                if (rand() % 2) { // a lot of noise to mess with PNG compression
                    imagesetpixel($big, $x, $y, $white);
                }
            }
        }

        imagepng($big, $target);
        $this->assertGreaterThan(1e5, filesize($target));

        $slim = $this->sut->slimPictureForPush(new SplFileInfo($target));
        $this->assertInstanceOf(SplFileInfo::class, $slim);
    }

    public function testClearCache()
    {
        $cacheDir = static::$kernel->getCacheDir();
        $this->sut->clear($cacheDir);
        $this->assertTrue(true);
    }

}
