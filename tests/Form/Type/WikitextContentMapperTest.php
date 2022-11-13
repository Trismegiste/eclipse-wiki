<?php

/*
 * eclipse-wiki
 */

use App\Entity\Scene;
use App\Form\Type\WikitextContentMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Twig\Environment;

class WikitextContentMapperTest extends TestCase
{

    protected $sut;
    protected $twig;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
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

        $obj = new Scene('old');
        $this->sut->mapFormsToData(new ArrayIterator([]), $obj);
        $this->assertEquals('content', $obj->getContent());
    }

}
