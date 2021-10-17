<?php

/*
 * Eclipse Wiki
 */

use App\Entity\MediaWikiPage;
use App\Entity\Skill;
use App\Repository\SkillProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Description of SkillProviderTest
 */
class SkillProviderTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        self::createKernel();
        $this->sut = self::getContainer()->get(SkillProvider::class);
    }

    public function testInsertData()
    {
        $repo = self::getContainer()->get('app.mwpage.repository');
        $it = $repo->search();
        $repo->delete(iterator_to_array($it));

        $dummy = new MediaWikiPage('Dummy', 'Compétence');
        $dummy->content = "{{SaWoCompétence|attr=int|core=0|src=SWADE}}\nKnowledge of scientific";
        $repo->save($dummy);
        $it = $repo->search();
        $this->assertCount(1, iterator_to_array($it));
    }

    public function testFindOne()
    {
        $skill = $this->sut->findOne('Dummy');
        $this->assertEquals('Dummy', $skill->getName());
    }

    public function testFindAll()
    {
        $skill = $this->sut->getListing();
        $this->assertCount(1, $skill);
        $this->assertArrayHasKey('Dummy', $skill);
        $this->assertInstanceOf(Skill::class, $skill['Dummy']);
        $this->assertEquals('Dummy', $skill['Dummy']->getName());
    }

}
