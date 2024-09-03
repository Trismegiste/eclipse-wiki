<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Synopsis;
use App\Service\Ollama\RequestFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A form for Synopsis entity
 */
class SynopsisType extends AbstractType
{

    public function __construct(protected string $ollamaApi, protected RequestFactory $payloadFactory)
    {
        
    }

    public function getParent(): ?string
    {
        return VertexType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->remove('content')
                ->add('pitch', TextareaType::class, ['attr' => [
                        'rows' => 4,
                        'x-model' => "scenario.pitch",
                        'class' => "pure-input-1",
                        'data-autofocus' => true
                    ]
                ])
                ->add('story', TextareaType::class)
                ->add('act', CollectionType::class, [
                    'entry_type' => TextareaType::class,
                    'entry_options' => [
                        'attr' => ['rows' => 3],
                        'required' => true,
                        'label' => false,
                        'attr' => [
                            'rows' => 10,
                            'class' => "pure-input-1"
                        ]
                    ],
                    'allow_add' => true
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Synopsis($form->get('title')->getData());
        });
        $resolver->setDefault('attr', [
            'class' => 'pure-form',
            'x-data' => "dramatron('{$this->ollamaApi}')"
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['default_payload'] = $this->payloadFactory->create("Dans le contexte précédemment décrit, voici le synopsis du roman\n");
    }

}
