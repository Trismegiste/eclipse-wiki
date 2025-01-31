<?php

use App\Form\Llm\PromptFormFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormView;

class PromptFormFactoryTest extends KernelTestCase
{

    protected PromptFormFactory $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(PromptFormFactory::class);
    }

    public function getContentPrompt()
    {
        return [
            ['npc-bg', ['title' => 'yolo']],
            ['bar', ['title' => 'yolo']],
            ['free', []]
        ];
    }

    /** @dataProvider getContentPrompt */
    public function testContentForm($key, array $prefill)
    {
        $form = $this->sut->createForContentGeneration($key, $prefill);
        $view = $form->createView();
        $this->assertInstanceOf(FormView::class, $view);
    }

    public function testPrefillTitle()
    {
        $form = $this->sut->createForContentGeneration('npc-bg', ['title' => 'yolo']);
        $this->assertEquals('yolo', $form['title']->getData());
        $view = $form->createView();
        $this->assertEquals('yolo', $view['title']->vars['data']);
    }

    public function testBadKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->sut->createForContentGeneration('missing', []);
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
