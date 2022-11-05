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
    /*   protected Environment $twig;
      protected string $template;

      public function __construct(Environment $twig, string $template)
      {
      $this->template = $template;
      $this->twig = $twig;
      }
     */

    public function mapDataToForms($viewData, Traversable $forms)
    {
        
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

        $fields = iterator_to_array($forms);
        ob_start();

        echo "==Décor==\n";
        echo '[[' . $fields['place']->getData() . "]]\n";
        echo "==Ambiance==\n";
        echo $fields['ambience']->getData() . PHP_EOL;
        echo "==Personnages==\n";
        foreach ($fields['npc']->getData() as $name) {
            echo "* [[$name]]\n";
        }
        if (!empty($fields['prerequisite']->getData())) {
            echo "==Prérequis==\n";
            echo $fields['prerequisite']->getData() . PHP_EOL;
        }
        echo "==Événements==\n";
        echo $fields['event']->getData() . PHP_EOL;
        echo "==Enjeu/Conséquences==\n";
        echo $fields['outcome']->getData() . PHP_EOL;

        $viewData->setContent(ob_get_clean());
    }

}
