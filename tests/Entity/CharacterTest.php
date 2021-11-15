<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Entity;

use App\Entity\Armor;
use App\Entity\Attack;
use App\Entity\Attribute;
use App\Entity\Character;
use App\Entity\Edge;
use App\Entity\Gear;
use App\Entity\Hindrance;
use App\Entity\Morph;
use App\Entity\Skill;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

abstract class CharacterTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = $this->createCharacter();
    }

    abstract function createCharacter(): Character;

    public function testMorph()
    {
        $m = $this->createStub(Morph::class);
        $this->sut->setMorph($m);
        $this->assertEquals($m, $this->sut->getMorph());
    }

    public function testSkills()
    {
        $this->assertCount(0, $this->sut->getSkills());
        $sk = $this->createStub(Skill::class);
        $this->sut->addSkill($sk);
        $this->assertCount(1, $this->sut->getSkills());
        $this->sut->removeSkill($sk);
        $this->assertCount(0, $this->sut->getSkills());
    }

    public function testAddSameSkill()
    {
        $sk = $this->createMock(Skill::class);
        $sk->expects($this->any())
                ->method('getName')
                ->willReturn('yolo');

        $this->sut->addSkill($sk);
        $this->assertCount(1, $this->sut->getSkills());
        $this->sut->addSkill($sk);
        $this->assertCount(1, $this->sut->getSkills());
    }

    public function testSearchSkill()
    {
        $this->sut->addSkill(new Skill('Yolo', 'DUM'));
        $this->assertEquals('Yolo', $this->sut->searchSkillByName('Yolo')->getName());
    }

    public function testParry()
    {
        $fight = new Skill('Combat', 'DUM');
        $fight->dice = 12;
        $fight->modifier = 2;
        $this->sut->addSkill($fight);
        $this->assertEquals(9, $this->sut->getParry());
    }

    public function testToughness()
    {
        $attr = new Attribute('Vigueur');
        $attr->dice = 6;
        $this->sut->attributes[] = $attr;
        $this->assertEquals(5, $this->sut->getToughness());
    }

    public function testNoAttribute()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->sut->getAttributeByName('DUMMY');
    }

    public function testNoSkill()
    {
        $this->assertNull($this->sut->searchSkillByName('Yolo'));
    }

    public function testGears()
    {
        $this->sut->setGears([new Gear()]);
        $this->assertCount(1, $this->sut->getGears());
    }

    public function testAttacks()
    {
        $this->sut->setAttacks([new Attack()]);
        $this->assertCount(1, $this->sut->getAttacks());
    }

    public function testArmors()
    {
        $this->sut->setArmors([new Armor()]);
        $this->assertCount(1, $this->sut->getArmors());
    }

    public function testArmorStacking()
    {
        $stack = [];
        for ($k = 0; $k < 4; $k++) {
            $armor = new Armor();
            $armor->protect = 4;
            $armor->zone = 'T';
            $stack[] = $armor;
        }
        $this->sut->setArmors($stack);
        $this->assertEquals(6, $this->sut->getTotalArmor());
    }

    public function testEdges()
    {
        $this->sut->setEdges([new Edge('smth', 'N', 'dum')]);
        $this->assertCount(1, $this->sut->getEdges());
    }

    public function testHindrances()
    {
        $this->sut->setHindrances([new Hindrance('smth')]);
        $this->assertCount(1, $this->sut->getHindrances());
    }

    public function testJsonAndBson()
    {
        $this->assertJson(json_encode($this->sut));
    }

    public function testClone()
    {
        $obj = clone $this->sut;
        $this->assertTrue($obj->isNew());
    }

    public function testAttributePoints()
    {
        $this->sut->attributes[] = new Attribute('dum');
        $this->sut->attributes[0]->dice = 12;
        $this->assertEquals(4, $this->sut->getAttributePoints());
    }

    public function testSkillPoints()
    {
        $sk = new Skill('test', 'cat');
        $sk->dice = 12;
        $this->sut->addSkill($sk);
        $this->assertEquals(5, $this->sut->getSkillPoints());
    }

    public function testPowerIndex()
    {
        $this->sut->attributes[] = new Attribute('dum');
        $this->sut->attributes[0]->dice = 12;
        $sk = new Skill('test', 'cat');
        $sk->dice = 12;
        $this->sut->addSkill($sk);

        $this->assertEquals(-5, $this->sut->getPowerIndex());
    }

    public function testSortingSkills()
    {
        $this->sut->addSkill(new Skill('zzzz', 'cat'));
        $this->sut->addSkill(new Skill('aaaa', 'cat'));
        $dump = $this->sut->bsonSerialize();
        $this->assertEquals('aaaa', $dump['skills'][0]->getName());
    }

}
