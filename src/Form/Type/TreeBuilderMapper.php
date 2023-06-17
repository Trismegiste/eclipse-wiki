<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Vertex;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Traversable;

/**
 * Builds a PlotNode tree from a bunch a string, only used for creation
 */
class TreeBuilderMapper implements DataMapperInterface
{

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        
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

        $viewData = new \App\Entity\PlotNode('Root');
        foreach ($forms as $child) {
            $label = $child->getData();
            if (!empty($label)) {
                $act = new \App\Entity\PlotNode($label);
                $viewData->nodes[] = $act;
            }
        }
    }

}
