<?php

/*
 * eclipse-wiki
 */

use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Transhuman;
use App\Service\AvatarMaker;
use PHPUnit\Framework\TestCase;

class AvatarMakerTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new AvatarMaker('./public/socnet/');
    }

    protected function createNpc(): Transhuman
    {
        $obj = new Transhuman('Alice Blue', $this->createStub(Background::class), $this->createStub(Faction::class));
        $obj->economy = ['Ressource' => 8, 'L\'Œil' => 4, 'CivicNet' => 6, 'Guanxi' => 9];

        return $obj;
    }

    public function testGenerate()
    {
        $npc = $this->createNpc();
        $res = $this->sut->generate($npc, imagecreatetruecolor(500, 500));
        $this->assertInstanceOf(\GdImage::class, $res);
        $this->assertEquals(503, imagesx($res));
        $this->assertEquals(894, imagesy($res));
    }

}
