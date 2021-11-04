<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\SaWoTrait;
use App\Repository\GenericProvider;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Traversable;

/**
 * Mapper for SaWoTraitType
 */
class SaWoTraitMapper implements DataMapperInterface
{

    protected $provider;

    public function __construct(GenericProvider $pro)
    {
        $this->provider = $pro;
    }

    public function mapDataToForms($viewData, Traversable $forms)
    {
        if (is_null($viewData)) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof SaWoTrait) {
            throw new UnexpectedTypeException($viewData, SaWoTrait::class);
        }

        $fields = iterator_to_array($forms);
        $fields['name']->setData($viewData->getName());
        $fields['roll']->setData($viewData->dice + $viewData->modifier);
    }

    public function mapFormsToData(Traversable $forms, &$attr)
    {
        $fields = iterator_to_array($forms);
        $attr = $this->provider->findOne($fields['name']->getData());
        $roll = $fields['roll']->getData();

        if ($roll > 12) {
            $attr->dice = 12;
            $attr->modifier = $roll - 12;
        } else {
            $attr->dice = $roll;
            $attr->modifier = 0;
        }
    }

}
