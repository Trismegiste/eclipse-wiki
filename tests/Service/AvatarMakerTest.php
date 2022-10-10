<?php

/*
 * eclipse-wiki
 */

use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Transhuman;
use App\Service\AvatarMaker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

class AvatarMakerTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $twig = static::getContainer()->get(Environment::class);
        $this->sut = new AvatarMaker($twig, static::getContainer()->getParameter('kernel.project_dir') . '/public');
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
        $res = $this->sut->generate($npc, imagecreatefrompng(__DIR__ . '/avatar.png'));
        $this->assertInstanceOf(GdImage::class, $res);
        $this->assertEquals(503, imagesx($res));
        $this->assertEquals(894, imagesy($res));
    }

}
