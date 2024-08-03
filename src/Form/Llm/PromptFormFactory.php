<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Llm;

use App\Entity\Vertex;
use App\Form\Llm\Sample\BarDescription;
use App\Form\Llm\Sample\NpcBackground;
use App\Service\Ollama\ParameterizedPrompt;
use InvalidArgumentException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * A factory for generating PromptType forms
 */
class PromptFormFactory
{

    const promptRepository = [
        'npc-bg' => NpcBackground::class,
        'bar' => BarDescription::class,
        'thing-name' => Sample\ThingName::class,
        'npc-name' => Sample\NpcName::class
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
    public function create(string $key, ?Vertex $vertex = null, array $options = []): FormInterface
    {
        $prefill = $this->createNewParameters();
        $fqcn = $this->getFormType($key);
        if (!is_null($vertex)) {
            $fqcn::initializeWithVertex($prefill, $vertex);
        }
        $prompt = $this->formFac->create($fqcn, $prefill, $options);

        return $prompt;
    }

    /**
     * Gets a title for the header of the LLM-generated content
     * @param string $key the key for the prompt
     * @return string
     */
    public function getSubtitle(string $key): string
    {
        $fqcn = $this->getFormType($key);

        return $fqcn::getContentTitle();
    }

    protected function createNewParameters(): ParameterizedPrompt
    {
        return new ParameterizedPrompt();
    }

    protected function getFormType(string $key): string
    {
        if (!key_exists($key, self::promptRepository)) {
            throw new InvalidArgumentException("$key is not a valid key");
        }

        $fqcn = self::promptRepository[$key];

        if (!is_a($fqcn, LlmContentInfo::class, true)) {
            throw new InvalidArgumentException("$fqcn does not implement LlmContentInfo");
        }

        return $fqcn;
    }

}
