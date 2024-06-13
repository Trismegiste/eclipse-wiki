<?php

/*
 * eclipse-wiki
 */

use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Transhuman;
use App\Service\AvatarMaker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AvatarMakerTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new AvatarMaker(static::getContainer()->getParameter('kernel.project_dir') . '/public');
    }

    protected function createNpc(): Transhuman
    {
        $obj = new Transhuman('Alice Blue', $this->createStub(Background::class), $this->createStub(Faction::class));
        $obj->economy = ['Ressource' => 8, 'L\'Œil' => 4, 'CivicNet' => 6, 'Guanxi' => 9];
        $obj->hashtag = '#php #symfony #mongodb #mercure #babylonjs #alpinejs';

        return $obj;
    }

    public function testGenerate()
    {
        $npc = $this->createNpc();
        $res = $this->sut->generate($npc, new SplFileInfo(__DIR__ . '/avatar.png'));
        $this->assertInstanceOf(SplFileInfo::class, $res);
        list($w, $h) = getimagesize($res->getPathname());
        $this->assertEquals(503, $w);
        $this->assertEquals(894, $h);
    }

}
