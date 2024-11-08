<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Llm;

use App\Form\Llm\Sample\BarDescription;
use App\Form\Llm\Sample\FreePrompt;
use App\Form\Llm\Sample\NpcBackground;
use App\Form\Llm\Sample\NpcName;
use App\Form\Llm\Sample\ThingName;
use InvalidArgumentException;
use Symfony\Component\Form\AbstractType;
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
        'thing-name' => ThingName::class,
        'npc-name' => NpcName::class,
        'free' => FreePrompt::class
    ];

    public function __construct(protected FormFactoryInterface $formFac)
    {
        
    }

    /**
     * Creates the form for generating a parameterized prompt for generating the LLM content
     * @param string $key the key for the prompt (see self::promptRepository above)
     * @param iterable $prefillParam parameters for prefilling the prompt form
     * @param array $options Options for the form
     * @return FormInterface Ready to use form
     */
    public function createForContentGeneration(string $key, iterable $prefillParam, array $options = []): FormInterface
    {
        $fqcn = $this->getFormType($key);
        if (!is_a($fqcn, AbstractType::class, true)) {
            throw new InvalidArgumentException("$fqcn is not an AbstractType");
        }

        $prompt = $this->formFac->create($fqcn, null, $options);
        // prefill the form
        foreach ($prefillParam as $key => $val) {
            $prompt[$key]->setData($val);
        }

        return $prompt;
    }

    public function createForListingGeneration(string $key, array $options = []): FormInterface
    {
        $fqcn = $this->getFormType($key);
        if (!is_a($fqcn, AbstractType::class, true)) {
            throw new InvalidArgumentException("$fqcn is not an AbstractType");
        }

        $prompt = $this->formFac->create($fqcn, null, $options);

        return $prompt;
    }

    protected function getFormType(string $key): string
    {
        if (!key_exists($key, self::promptRepository)) {
            throw new InvalidArgumentException("$key is not a valid key");
        }

        return self::promptRepository[$key];
    }

}
