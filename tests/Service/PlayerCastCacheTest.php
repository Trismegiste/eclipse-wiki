<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\PlayerCastCache;

class PlayerCastCacheTest extends KernelTestCase
{

    protected $sut;

    public function setUp(): void
    {
        $this->sut = static::getContainer()->get(PlayerCastCache::class);
    }

    public function testSlimPicture()
    {
        $side = 2500;
        $big = imagecreatetruecolor($side, $side);
        $target = sys_get_temp_dir() . '/big.png';
        imagepng($big, $target);
        $this->assertGreaterThan(1e5, filesize($target));

        $slim = $this->sut->slimPictureForPush(new SplFileInfo($target));
        $this->assertInstanceOf(SplFileInfo::class, $slim);
    }

}
