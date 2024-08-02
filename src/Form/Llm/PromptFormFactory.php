<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Llm;

use App\Entity\Vertex;
use App\Form\Llm\Sample\BackgroundPromptType;
use App\Form\Llm\Sample\BarPromptType;
use App\Service\Ollama\ParameterizedPrompt;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * A factory for generating PromptType forms
 */
class PromptFormFactory
{

    const promptRepository = [
        'npc-bg' => [
            'type' => BackgroundPromptType::class,
            'subtitle' => 'Background'
        ],
        'bar' => [
            'type' => BarPromptType::class,
            'subtitle' => 'Description'
        ]
    ];

    public function __construct(protected FormFactoryInterface $formFac)
    {
        
    }

    /**
     * Creates the form for generating a parameterized prompt for generating the LLM content
     * @param string $key the key for the prompt (see self::promptRepository above)
     * @param Vertex $vertex the object from the content is generated, it is useful to initialize some filed in the parameterized prompt
     * @param array $options Options for the form
     * @return FormInterface Ready to use form
     */
    public function create(string $key, Vertex $vertex, array $options = []): FormInterface
    {
        $prefill = new ParameterizedPrompt();
        $prefill->param['title'] = $vertex->getTitle();  // @todo this line, probably, would be replaced by a closure (this line will become the default behavior)
        $prompt = $this->formFac->create(self::promptRepository[$key]['type'], $prefill, $options);

        return $prompt;
    }

    /**
     * Gets a title for the header of the LLM-generated content
     * @param string $key the key for the prompt
     * @return string
     */
    public function getSubtitle(string $key): string
    {
        return self::promptRepository[$key]['subtitle'];
    }

}
