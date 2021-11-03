<?php

/*
 * Eclipse Wiki
 */

use App\Entity\Skill;
use App\Form\Type\SkillType;
use App\Repository\SkillProvider;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Description of SkillTypeTest
 */
class SkillTypeTest extends TypeTestCase
{

    private $provider;

    protected function setUp(): void
    {
        // mock any dependencies
        $this->provider = $this->createMock(SkillProvider::class);
        $this->provider->expects($this->any())
            ->method('findOne')
            ->willReturnCallback(function($name) {
                return new Skill($name, 'DUM');
            });

        parent::setUp();
    }

    protected function getExtensions()
    {
        // create a type instance with the mocked dependencies
        $type = new SkillType($this->provider);

        return [
            // register the type instances with the PreloadedExtension
            new PreloadedExtension([$type], []),
        ];
    }

    public function testEmpty()
    {
        $form = $this->factory->create(SkillType::class);
        $form->submit(['name' => 'Essai', 'roll' => 13]);
        $skill = $form->getData();
        $this->assertEquals('Essai', $skill->getName());
        $this->assertEquals(12, $skill->dice);
        $this->assertEquals(1, $skill->modifier);
    }

    public function testSameName()
    {
        $old = new Skill('Essai', 'YOLO');
        $form = $this->factory->create(SkillType::class, $old);
        $form->submit(['name' => 'Essai', 'roll' => 6]);
        $skill = $form->getData();
        $this->assertEquals('Essai', $skill->getName());
        $this->assertEquals(6, $skill->dice);
        $this->assertEquals(0, $skill->modifier);
    }

    public function testChangedName()
    {
        $old = new Skill('Essai', 'YOLO');
        $form = $this->factory->create(SkillType::class, $old);
        $form->submit(['name' => 'Tir', 'roll' => 8]);
        $skill = $form->getData();
        $this->assertEquals('Tir', $skill->getName());
        $this->assertEquals(8, $skill->dice);
        $this->assertEquals(0, $skill->modifier);
    }

}
