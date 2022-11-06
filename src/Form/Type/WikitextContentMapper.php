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

    public function mapDataToForms($viewData, Traversable $forms)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        if (!$viewData instanceof Vertex) {
            throw new UnexpectedTypeException($viewData, Vertex::class);
        }

        $forms = iterator_to_array($forms);
        $forms['title']->setData($viewData->getTitle());
    }

    public function mapFormsToData(Traversable $forms, &$viewData)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        if (!$viewData instanceof Vertex) {
            throw new UnexpectedTypeException($viewData, Vertex::class);
        }

        $fields = [];
        foreach ($forms as $key => $widget) {
            $fields[$key] = $widget->getData();
        }

        $viewData->setContent($this->twig->render($this->template, $fields));
    }

}
