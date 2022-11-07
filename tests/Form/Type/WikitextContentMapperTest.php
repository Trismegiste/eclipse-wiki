<?php

use App\Form\Type\WikitextContentMapper;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use PHPUnit\Framework\TestCase;

class WikitextContentMapperTest extends TestCase
{
    protected $sut;
    protected $twig;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(\Twig\Environment::class);
        $this->sut = new WikitextContentMapper($this->twig, 'dummy.wiki.twig');
    }

    public function testFormToDataWithNull()
    {
        $this->twig->expects($this->never())
              ->method('render');
        $obj = null;
        $this->sut->mapFormsToData(new ArrayIterator([]), $obj);
    }

    public function testFormToDataWithBadType()
    {
        $this->expectException(UnexpectedTypeException::class);

        $obj = new stdClass();
        $this->sut->mapFormsToData(new ArrayIterator([]), $obj);
    }

    public function testFormToDataValid()
    {
        $this->twig->expects($this->once())
              ->method('render')
              ->willReturn('content');

        $obj = new App\Entity\Vertex('old');
        $this->sut->mapFormsToData(new ArrayIterator([]), $obj);
        $this->assertEquals('content', $obj->getContent());
    }

}
