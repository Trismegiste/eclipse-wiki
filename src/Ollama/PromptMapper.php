<?php

/*
 * eclipse-wiki
 */

namespace App\Ollama;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;
use Traversable;
use Twig\Environment;

/**
 * Mapper param prompt array <-> ParameterizedPrompt
 */
class PromptMapper implements DataMapperInterface
{

    public function __construct(protected string $template, protected Environment $twig)
    {
        
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if (is_null($viewData)) {
            return;
        }

        if (!$viewData instanceof ParameterizedPrompt) {
            throw new UnexpectedTypeException($viewData, ParameterizedPrompt::class);
        }

        foreach ($forms as $key => $field) {
            /** @var FormInterface $field */
            $field->setData($viewData->param[$key]);
        }
    }

    public function mapFormsToData(Traversable $forms, &$viewData): void
    {

        if (!$viewData instanceof ParameterizedPrompt) {
            throw new UnexpectedTypeException($viewData, ParameterizedPrompt::class);
        }

        foreach ($forms as $key => $field) {
            /** @var FormInterface $field */
            $viewData->param[$key] = $field->getData();
        }

        $wrapped = $this->twig->createTemplate($this->template, self::class);  // this is bad practice but it works
        $viewData->prompt = $wrapped->render($viewData->param);
    }

}
