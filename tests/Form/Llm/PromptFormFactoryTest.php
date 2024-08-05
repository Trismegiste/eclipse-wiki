<?php

use App\Service\Ollama\ParameterizedPrompt;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Form\Llm\PromptFormFactory;
use App\Entity\Scene;
use Symfony\Component\Form\FormView;

class PromptFormFactoryTest extends KernelTestCase
{
    protected PromptFormFactory $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(App\Form\Llm\PromptFormFactory::class);
    }


    public function getContentPrompt()
    {
        return [
            ['npc-bg'],
            ['bar'],
            ['free']
        ];
    }

    /** @dataProvider getContentPrompt */
    public function testContentForm($key)
    {
        $v = new Scene('yolo');
        $form = $this->sut->createForContentGeneration($key, $v);
        $view = $form->createView();
        $this->assertInstanceOf(FormView::class, $view);
    }

    public function testBadKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $v = new Scene('yolo');
        $form = $this->sut->createForContentGeneration('missing', $v);
    }

    public function getListingPrompt()
    {
        return [
            ['npc-name'],
            ['thing-name'],
        ];
    }

    /** @dataProvider getListingPrompt */
    public function testListingForm($key)
    {
        $form = $this->sut->createForListingGeneration($key);
        $view = $form->createView();
        $this->assertInstanceOf(FormView::class, $view);
    }

}
