<?php

use App\Form\SceneCreate;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Form;

class SceneCreateTest extends KernelTestCase
{

    /** @var Form */
    protected $sut;

    protected function setUp(): void
    {
        $factory = static::getContainer()->get('form.factory');
        $this->sut = $factory->create(SceneCreate::class);
    }

    public function testClean()
    {
        $repo = static::getContainer()->get(VertexRepository::class);
        $repo->delete(iterator_to_array($repo->search()));
        $this->assertCount(0, iterator_to_array($repo->search()));
    }

    public function testCreate()
    {
        
    }

}
