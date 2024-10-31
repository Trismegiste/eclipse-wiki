<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Form\Type\WikitextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Quick info fields for a NPC that compile into the content
 */
class NpCreationInfoType extends AbstractType implements \Symfony\Component\Form\DataMapperInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('info', WikitextType::class, [
                    'required' => false,
                    'attr' => [
                        'class' => 'pure-input-1',
                        'placeholder' => 'Information au format WikiText',
                        'rows' => 2
                    ]
                ])
                ->setDataMapper($this)
        ;
    }

    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        
    }

    public function mapFormsToData(\Traversable $forms, &$viewData): void
    {
        
    }

}
