<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Encounter;
use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for creating and editing a Encounter
 */
class EncounterType extends AbstractType
{

    protected $repository;

    public function __construct(VertexRepository $repo)
    {
        $this->repository = $repo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('npc', ChoiceType::class, [
            'choices' => $this->repository->findByClass([\App\Entity\Ali::class, \App\Entity\Transhuman::class]),
            'expanded' => true,
            'multiple' => true,
            'choice_label' => ChoiceList::label($this, 'title'),
            'choice_value' => ChoiceList::value($this, 'pk'),
            'mapped' => false
        ]);
        if ($options['edit']) {
            $builder->remove('title');
            $builder->setMethod('PUT');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Encounter($form->get('title')->getData());
        });
        $resolver->setDefault('edit', false);
    }

    public function getParent()
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['edit']) {
            $view['create']->vars['label'] = 'Edit';
        }
        $view['content']->vars['attr']['rows'] = 5;
        parent::finishView($view, $form, $options);
        $this->moveAtEnd($view->children, 'create');
    }

    private function moveAtEnd(array &$arr, string $key): void
    {
        $item = $arr[$key];
        unset($arr[$key]);
        array_push($arr, $item);
    }

}
