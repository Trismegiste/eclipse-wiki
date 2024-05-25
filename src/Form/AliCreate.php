<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Ali;
use App\Form\Type\ProviderChoiceType;
use App\Form\Type\WikiTitleType;
use App\Repository\CharacterFactory;
use App\Repository\ShellProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Creation of an A.L.I
 */
class AliCreate extends AbstractType
{

    protected $shell;
    protected $factory;

    public function __construct(ShellProvider $morph, CharacterFactory $factory)
    {
        $this->shell = $morph;
        $this->factory = $factory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('wildCard', CheckboxType::class, ['required' => false])
                ->add('title', WikiTitleType::class, ['attr' => ['placeholder' => 'Choisissez un nom']])
                ->add('morph', ProviderChoiceType::class, ['provider' => $this->shell, 'placeholder' => '--- Choisissez une coquille ---'])
                ->add('generate', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Ali::class);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            $name = $form->get('title')->getData();
            return $this->factory->createAli($name);
        });
    }

    public function getBlockPrefix(): string
    {
        return 'ali';
    }

}
