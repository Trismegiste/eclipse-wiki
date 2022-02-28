<?php

use App\Entity\MediaWikiPage;
use App\Form\NpcStats;
use App\Repository\CharacterFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Form;

/*
 * eclipse-wiki
 */

class NpcStatsTest extends KernelTestCase
{

    /** @var Form */
    protected $sut;

    protected function setUp(): void
    {
        $npcFactory = static::getContainer()->get(CharacterFactory::class);
        $factory = static::getContainer()->get('form.factory');

        $this->sut = $factory->create(NpcStats::class, $npcFactory->createFreeform('Sample', 'Unknown'));
    }

    public function testInsertData()
    {
        $repo = self::getContainer()->get('app.mwpage.repository');
        $it = $repo->search();
        $repo->delete(iterator_to_array($it));

        $edge = new MediaWikiPage('Edge1', 'Atout');
        $hind = new MediaWikiPage('Hind1', 'Handicap');
        $edge->content = "aaa{{SaWoAtout|ego=1|bio=1|synth=1|rang=n|type=bak|src=EP}}bbbb{{PrérequisAtout|INT d8}}ccccc";
        $hind->content = "zzzzzzzz {{SaWoHandicap|ego=1|bio=1|synth=1|type=M}}";

        $repo->save([$edge, $hind]);

        $it = $repo->search();
        $this->assertCount(2, iterator_to_array($it));
    }

    public function getFixture(): array
    {
        return [
            [
                [
                    'attributes' => [
                        ['name' => 'Force', 'roll' => 12],
                        ['name' => 'Agilité', 'roll' => 10],
                        ['name' => 'Vigueur', 'roll' => 8],
                        ['name' => 'Âme', 'roll' => 6],
                        ['name' => 'Intellect', 'roll' => 4]
                    ],
                    'edges' => [
                        ['name' => 'Edge1', 'origin' => 'Slot']
                    ],
                    'hindrances' => [
                        ['name' => 'Hind1', 'origin' => 'Morphe', 'level' => 2]
                    ]
                ]
            ]
        ];
    }

    /** @dataProvider getFixture */
    public function testEditSubmit($data)
    {
        $this->sut->submit($data);

        $npc = $this->sut->getData();
        $this->assertTrue($this->sut->isSynchronized());
        $this->assertEquals('Sample', $npc->getTitle());
    }

    /** @dataProvider getFixture */
    public function testCustomFormView($data)
    {
        $this->sut->submit($data);

        $view = $this->sut->createView();
        $this->assertCount(1, $view['edges']->children);
    }

}
