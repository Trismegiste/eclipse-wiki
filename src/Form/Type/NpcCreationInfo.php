<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\Place;
use App\Form\Type\WikitextType;
use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Traversable;

/**
 * Quick info fields for a NPC that compile into the content
 */
class NpcCreationInfo extends AbstractType implements DataMapperInterface
{

    public function __construct(protected VertexRepository $repository)
    {
        
    }

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
                ->add('habitat', AutocompleteType::class, [
                    'choices' => $this->getPlaceTitle(),
                    'required' => false
                ])
                ->add('work', AutocompleteType::class, [
                    'choices' => $this->getPlaceTitle(),
                    'required' => false
                ])
                ->add('fun', AutocompleteType::class, [
                    'choices' => $this->getPlaceTitle(),
                    'required' => false
                ])
                ->setDataMapper($this)
        ;
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        
    }

    public function mapFormsToData(Traversable $forms, &$viewData): void
    {
        if (is_null($viewData)) {
            return;
        }

        $field = iterator_to_array($forms);
        $viewData = $field['info']->getData()
                . "\n"
                . $this->getFilledTemplate($field['work']->getData(),
                        $field['fun']->getData(),
                        $field['habitat']->getData());
    }

    protected function getFilledTemplate(?string $work, ?string $fun, ?string $habitat): string
    {
        return "{{location\n|habitat=$habitat\n|work=$work\n|fun=$fun\n}}";
    }

    protected function getPlaceTitle(): iterable
    {
        // @TODO add a method searchOnlyTitleByClass. Also usable in SceneContentWizardType
        $choice = [];
        foreach ($this->repository->findByClass(Place::class) as $vertex) {
            $choice[] = $vertex->getTitle();
        }

        return $choice;
    }

}
