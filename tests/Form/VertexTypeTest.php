<?php

/*
 * eclipse-wiki
 */

use App\Entity\Vertex;
use App\Form\VertexType;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class VertexTypeTest extends KernelTestCase
{

    /** @var \Symfony\Component\Form\Form */
    protected $sut;

    protected function setUp(): void
    {
        $factory = static::getContainer()->get('form.factory');

        $this->sut = $factory->create(VertexType::class);
    }

    public function testClean()
    {
        $repo = static::getContainer()->get(VertexRepository::class);
        $repo->delete(iterator_to_array($repo->search()));
        $this->assertCount(0, iterator_to_array($repo->search()));
    }

    protected function getInputData(): array
    {
        return [
            'title' => 'yolo',
            'content' => 'some text'
        ];
    }

    public function testEmpty()
    {
        $this->sut->submit($this->getInputData());
        $this->assertTrue($this->sut->isSynchronized());
        $this->assertTrue($this->sut->isValid());

        $model = $this->sut->getData();
        $this->assertInstanceOf(Vertex::class, $model);
        $this->assertEquals('yolo', $model->getTitle());

        $repo = static::getContainer()->get(VertexRepository::class);
        $repo->save($this->sut->getData());
    }

    public function testUniqueTitleFail()
    {
        $this->sut->submit($this->getInputData());
        $this->assertTrue($this->sut->isSynchronized());
        $this->assertFalse($this->sut->isValid());
        $this->assertCount(1, $this->sut['title']->getErrors());
        $this->assertStringContainsString('already exist', $this->sut['title']->getErrors()[0]->getMessage());
    }

}
