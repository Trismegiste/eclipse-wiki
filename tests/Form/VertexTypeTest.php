<?php

/*
 * eclipse-wiki
 */

use App\Entity\Scene;
use App\Form\VertexType;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;

class VertexTypeTest extends KernelTestCase
{

    /** @var Form */
    protected $sut;
    protected VertexRepository $repo;
    protected FormFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->factory = static::getContainer()->get('form.factory');
        $this->sut = $this->factory->create(VertexType::class);
        $this->repo = static::getContainer()->get(VertexRepository::class);
    }

    public function testClean()
    {
        $this->repo->delete(iterator_to_array($this->repo->search()));
        $this->assertCount(0, iterator_to_array($this->repo->search()));
    }

    public function testEdit()
    {
        $sample = new Scene('sample');
        $this->sut = $this->factory->create(VertexType::class, $sample, ['method' => 'PUT']);
        $this->sut->submit(['content' => 'sample text']);
        $this->assertEquals('Sample', $sample->getTitle());
        $this->assertEquals('sample text', $sample->getContent());
        $this->repo->save($this->sut->getData());
    }

    public function testUniqueTitleFail()
    {
        $sample = new Scene('sample');
        $this->sut = $this->factory->create(VertexType::class, $sample);
        $this->sut->submit(['title' => 'sample', 'content' => 'dummy']);
        $this->assertTrue($this->sut->isSynchronized());
        $this->assertFalse($this->sut->isValid());
        $this->assertCount(1, $this->sut['title']->getErrors());
        $this->assertStringContainsString('already exist', $this->sut['title']->getErrors()[0]->getMessage());
    }

}
