<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Character;
use Symfony\Component\Form\DataMapperInterface;
use Traversable;

/**
 * Synchronize a Character with a Character template
 */
class ResyncMapper implements DataMapperInterface
{

    public function __construct(protected Character $template)
    {
        
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        
    }

    public function mapFormsToData(Traversable $forms, &$viewData): void
    {
        /** @var Character $viewData */
        /** @var \Symfony\Component\Form\FormInterface $widget */
        $widget = iterator_to_array($forms);

        // Attributes synchronisation
        if ($widget['attributes']->getData()) {
            // Technically, it should be a deep cloning but since the object is saved into MongoDb and lost after, it's not a problem - be carefull though
            $viewData->attributes = $this->template->attributes;
        }

        // Skills synchronisation - same concerns about deep cloning as above
        if ($widget['skills']->getData()) {
            foreach ($viewData->getSkills() as $skill) {
                $viewData->removeSkill($skill);
            }
            foreach ($this->template->getSkills() as $skill) {
                $viewData->addSkill($skill);
            }
        }
    }

}
