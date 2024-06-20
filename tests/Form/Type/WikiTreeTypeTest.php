<?php

/*
 * eclipse-wiki
 */

use App\Entity\PlotNode;
use App\Form\Type\WikiTreeType;
use Symfony\Component\Form\Test\TypeTestCase;

class WikiTreeTypeTest extends TypeTestCase
{

    public function testSingleNode(): void
    {
        $form = $this->factory->create(WikiTreeType::class);
        $form->submit('{"data":{"title":"submitted", "finished":false}, "nodes":[]}');
        $this->assertTrue($form->isSynchronized(), $form->getErrors(true, true));
        $this->assertInstanceOf(PlotNode::class, $form->getData());
        $this->assertEquals('submitted', $form->getData()->title);
    }

    public function testEmptyJson(): void
    {
        $form = $this->factory->create(WikiTreeType::class);
        $form->submit('');
        $this->assertFalse($form->isSynchronized());
        $this->assertStringContainsString('invalid', $form->getErrors(true, true));
    }

    public function testInvalidJson(): void
    {
        $form = $this->factory->create(WikiTreeType::class);
        $form->submit('{"data":{"title":"submitted", "finish');
        $this->assertFalse($form->isSynchronized());
        $this->assertStringContainsString('invalid', $form->getErrors(true, true));
    }

}
