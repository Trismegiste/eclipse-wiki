<?php

/*
 * eclipse-wiki
 */

use App\Service\BoringAvatar;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BoringAvatarTest extends KernelTestCase
{

    protected $sut;
    protected $twig;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(BoringAvatar::class);
    }

    public function testCreateAvatar()
    {
        $svg = $this->sut->createBauhaus('yolo');
        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringEndsNotWith("</svg>", $svg);
    }

}
