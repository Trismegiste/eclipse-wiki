<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Vertex;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Traversable;
use Twig\Environment;

/**
 * Dump a template form to the wikitext content of a Vertex
 */
class WikitextContentMapper implements DataMapperInterface
{

    protected Environment $twig;
    protected string $template;

    public function __construct(Environment $twig, string $template)
    {
        $this->template = $template;
        $this->twig = $twig;
    }

    public function mapDataToForms($viewData, Traversable $forms): void
    {
        // there is no data yet, so nothing to prepopulate
    }

    public function mapFormsToData(Traversable $forms, &$viewData): void
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        if (!is_array($viewData)) {
            throw new UnexpectedTypeException($viewData, 'array');
        }

        $fields = [];
        foreach ($forms as $key => $widget) {
            $fields[$key] = $widget->getData();
        }

        $viewData = $this->twig->render($this->template, $fields);
    }

}
