<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Repository\TraitProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Traversable;

/**
 * Form for 8 Economies
 */
class EconomyType extends AbstractType implements DataMapperInterface
{

    protected $economyLabel = [];

    public function __construct(TraitProvider $provider)
    {
        $eco = array_values($provider->findSocialNetworks());
        array_unshift($eco, 'Ressource');
        foreach ($eco as $idx => $lbl) {
            $this->economyLabel["economy_$idx"] = $lbl;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->economyLabel as $field => $label) {
            $builder->add($field, IntegerType::class, [
                'required' => false,
                'label' => $label,
                'attr' => ['class' => 'input-1-4']
            ]);
        }
        $builder->setDataMapper($this);
    }

    public function mapDataToForms($viewData, Traversable $forms)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!is_array($viewData)) {
            throw new UnexpectedTypeException($viewData, 'array');
        }

        /** @var FormInterface $field */
        foreach ($forms as $field) {
            $label = $field->getConfig()->getOption('label');
            if (array_key_exists($label, $viewData)) {
                $field->setData($viewData[$label]);
            }
        }
    }

    public function mapFormsToData(Traversable $forms, &$viewData)
    {
        /** @var FormInterface $field */
        foreach ($forms as $field) {
            $label = $field->getConfig()->getOption('label');
            $viewData[$label] = $field->getData();
        }
    }

}
