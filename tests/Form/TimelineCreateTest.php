<?php

use App\Form\TimelineCreate;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Form;

class TimelineCreateTest extends KernelTestCase
{

    /** @var Form */
    protected $sut;

    protected function setUp(): void
    {
        $factory = static::getContainer()->get('form.factory');
        $this->sut = $factory->create(TimelineCreate::class);
    }

    public function testClean()
    {
        $repo = static::getContainer()->get(VertexRepository::class);
        $repo->delete(iterator_to_array($repo->search()));
        $this->assertCount(0, iterator_to_array($repo->search()));
    }

    public function testSubmitOk()
    {
        $this->sut->submit([
            "title" => "Scénario",
            "elevator_pitch" => 'Résumé',
            'scene' => ['Scène1','Scène2']
        ]);
        $this->assertTrue($this->sut->isSynchronized());
        $this->assertTrue($this->sut->isValid(), $this->sut->getErrors(true, true));
        $data = $this->sut->getData();
        $this->assertInstanceOf(App\Entity\Timeline::class, $data);
        $this->assertEquals('Scénario', $data->getTitle());
        $this->assertStringContainsString('Résumé', $data->getContent());
        $this->assertStringContainsString('==Timeline==', $data->getContent());
        $this->assertStringContainsString('* {{task|Scène1', $data->getContent());
    }

}
