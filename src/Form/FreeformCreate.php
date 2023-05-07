<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Freeform;
use App\Entity\Vertex;
use App\Repository\CharacterFactory;
use App\Validator\UniqueVertexTitle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * A form type for creating a free from NPC
 */
class FreeformCreate extends AbstractType
{

    protected $factory;

    public function __construct(CharacterFactory $factory)
    {
        $this->factory = $factory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('wildCard', CheckboxType::class, ['required' => false])
                ->add('title', TextType::class, [
                    'attr' => ['placeholder' => 'Choisissez un nom'],
                    'constraints' => [
                        new Regex(Vertex::FORBIDDEN_REGEX_TITLE, match: false),
                        new NotBlank(),
                        new UniqueVertexTitle()
                    ]
                ])
                ->add('type', Type\FullTextChoice::class, ['category' => 'freeform_type',
                    'placeholder' => '-------------',
                    'mapped' => false
                ])
                ->add('create', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Freeform::class);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            $name = $form->get('title')->getData();
            $type = $form->get('type')->getData();

            return $this->factory->createFreeform($name, $type);
        });
    }

}
