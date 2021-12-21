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
        $this->sut = new AvatarMaker();
    }

    protected function createNpc(): Transhuman
    {
        $obj = new Transhuman('Motoko', $this->createStub(Background::class), $this->createStub(Faction::class));
        $obj->economy = ['Toto' => 1, 'Yolo' => 2, 'Zog' => 3];

        return $obj;
    }

    public function testFindImage()
    {
        $npc = $this->createNpc();
        $npc->setContent('aaaa [[file:image.jpg]] [[link-avatar.jpg]] [[file:avatar.jpg]] end');

        $found = $this->sut->getImageChoice($npc);
        $this->assertCount(2, $found);
    }

    public function testGenerate()
    {
        $target = 'dump.jpg';
        if (file_exists($target)) {
            unlink($target);
        }

        $npc = $this->createNpc();
        $npc->economy;
        $res = $this->sut->generate($npc, join_paths(__DIR__, 'avatar.png'));
        imagejpeg($res, $target);
        $this->assertFileExists($target);
        unlink($target);
    }

}
