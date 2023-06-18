<?php

use App\Entity\Timeline;
use App\Form\TimelineCreate;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Form;
use Trismegiste\Strangelove\MongoDb\Repository;

class TimelineCreateTest extends KernelTestCase
{

    /** @var Form */
    protected $sut;
    protected Repository $repo;

    protected function setUp(): void
    {
        $this->repo = static::getContainer()->get(VertexRepository::class);
        $factory = static::getContainer()->get('form.factory');
        $this->sut = $factory->create(TimelineCreate::class);
    }

    public function testClean()
    {
        $this->repo->delete(iterator_to_array($this->repo->search()));
        $this->assertCount(0, iterator_to_array($this->repo->search()));
    }

    public function testSubmitOk()
    {
        $this->sut->submit([
            "title" => "Scénario",
            "elevatorPitch" => 'Résumé',
            'tree' => ['Scène1', 'Scène2']
        ]);
        $this->assertTrue($this->sut->isSynchronized());
        $this->assertTrue($this->sut->isValid(), $this->sut->getErrors(true, true));
        $data = $this->sut->getData();
        $this->repo->save($data);
        $this->assertInstanceOf(Timeline::class, $data);
        $this->assertEquals('Scénario', $data->getTitle());
        $this->assertStringContainsString('Résumé', $data->getContent());
        $this->assertStringContainsString('==Timeline==', $data->getContent());
        $this->assertStringContainsString('* Scène1', $data->getContent());
    }

}
