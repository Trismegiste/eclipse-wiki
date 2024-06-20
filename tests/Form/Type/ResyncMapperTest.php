<?php

/*
 * eclipse-wiki
 */

use App\Entity\Background;
use App\Entity\Edge;
use App\Entity\Faction;
use App\Entity\Skill;
use App\Entity\Transhuman;
use App\Form\Type\ResyncMapper;
use App\Repository\CharacterFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;

class ResyncMapperTest extends KernelTestCase
{

    protected Transhuman $template;
    protected Transhuman $instance;
    protected ResyncMapper $sut;

    protected function setUp(): void
    {
        $fac = static::getContainer()->get(CharacterFactory::class);

        $template = $fac->create('Jango', new Background('Mandalorean'), new Faction('Death Watch'));
        $instance = $fac->createExtraFromTemplate($template, 'Boba');
        // As if objects are extracted from the database (meaning all attributes are now deep cloned)
        $this->template = \MongoDB\BSON\toPHP(\MongoDB\BSON\fromJSON(\MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($template))));
        $this->instance = \MongoDB\BSON\toPHP(\MongoDB\BSON\fromJSON(\MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($instance))));
        $this->sut = new ResyncMapper($this->template);
    }

    protected function createFakeForm(array $checked): Iterator
    {
        $checked = array_merge([
            'attributes' => false,
            'skills' => false,
            'edges' => false,
            'economy' => false,
            'attacks' => false,
            'armors' => false
                ], $checked);

        $form = [];
        foreach ($checked as $key => $flag) {
            $checkbox = $this->createMock(FormInterface::class);
            $checkbox->expects($this->atLeastOnce())
                    ->method('getData')
                    ->willReturn($flag);
            $form[$key] = $checkbox;
        }

        return new ArrayIterator($form);
    }

    public function testCloneAttribute()
    {
        // update the template
        $attr = $this->template->getAttributeByName('Agilité');
        $attr->dice = 12;

        // check if objects are really deep-cloned, if we change the template, the instance is unchanged
        $this->assertNotEquals(12, $this->instance->getAttributeByName('Agilité')->dice);
        // sync the instance
        $this->sut->mapFormsToData($this->createFakeForm(['attributes' => true]), $this->instance);
        // check if instance is in synch with template
        $this->assertEquals(12, $this->instance->getAttributeByName('Agilité')->dice);
    }

    public function testCloneSkill()
    {
        // update the template
        $sk = new Skill('Tir', 'AGI');
        $sk->dice = 12;
        $this->template->addSkill($sk);

        // check if objects are really deep-cloned, if we change the template, the instance is unchanged
        $this->assertEquals(null, $this->instance->searchSkillByName('Tir'));
        // sync the instance
        $this->sut->mapFormsToData($this->createFakeForm(['skills' => true]), $this->instance);
        // check if instance is in synch with template
        $this->assertEquals(12, $this->instance->searchSkillByName('Tir')->dice);
    }

    public function testCloneEdge()
    {
        // update the template
        $e = new Edge('Fast draw', 'A', 'Cbt');
        $this->template->setEdges([$e]);

        // check if objects are really deep-cloned, if we change the template, the instance is unchanged
        $this->assertCount(0, $this->instance->getEdges());
        // add an edge that will be overriden by the resync, it's the intended behavior
        $this->instance->setEdges([new Edge('Discarded', 'N', 'Dum')]);
        // sync the instance
        $this->sut->mapFormsToData($this->createFakeForm(['edges' => true]), $this->instance);
        // check if instance is in synch with template
        $this->assertCount(1, $this->instance->getEdges());
        $this->assertEquals('Fast draw', $this->instance->getEdges()[0]->getName());
    }

    public function testCloneEconomy()
    {
        $this->template->economy = ['old' => 4];
        $this->sut->mapFormsToData($this->createFakeForm(['economy' => true]), $this->instance);
        $this->assertEquals(4, $this->instance->economy['old']);
    }

    public function testCloneAttack()
    {
        $att = new App\Entity\Attack();
        $att->title = 'gun';
        $this->template->setAttacks([$att]);
        $this->sut->mapFormsToData($this->createFakeForm(['attacks' => true]), $this->instance);
        $this->assertCount(1, $this->instance->getAttacks());
    }

    public function testCloneArmor()
    {
        $a = new App\Entity\Armor('beskar');
        $this->template->setArmors([$a]);
        $this->sut->mapFormsToData($this->createFakeForm(['armors' => true]), $this->instance);
        $this->assertEquals('beskar', $this->instance->getArmors()[0]->name);
    }

}
