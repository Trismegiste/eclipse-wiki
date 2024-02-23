<?php

/*
 * eclipse-wiki
 */

namespace App\Form\QuickNpc;

use Symfony\Component\Form\DataMapperInterface;
use Traversable;

/**
 * Mapper to transform creation data from quick NPC graph into array for Transhuman entity
 */
class SocNetMapper implements DataMapperInterface
{

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        
    }

    public function mapFormsToData(Traversable $forms, &$viewData): void
    {
        $viewData = [];
        foreach ($forms as $child) {
            /** @var \Symfony\Component\Form\FormInterface $child */
            $key = $child->get('key')->getData();
            $value = $child->get('value')->getData();
            if (!empty($key)) {
                $viewData[$key] = $value;
            }
        }
    }

}
