<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Place;
use App\Entity\Scene;
use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

/**
 * Description of SceneCreate
 *
 * @author florent
 */
class SceneCreate extends AbstractType implements DataMapperInterface
{

    use FormTypeUtils;

    protected VertexRepository $repository;

    public function __construct(VertexRepository $repo)
    {
        $this->repository = $repo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('content');
        $builder->add('place', Type\AutocompleteType::class, ['choices' => $this->getPlaceTitle()])
                ->add('ambience', TextareaType::class, ['attr' => ['rows' => 4]])
                ->add('npc1', Type\AutocompleteType::class, ['choices' => $this->getNpcTitle()])
        ;
        $builder->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Scene($form->get('title')->getData());
        });
    }

    public function getParent(): ?string
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->moveChildAtEnd($view, 'create');
    }

    protected function getPlaceTitle(): iterable
    {
        foreach ($this->repository->findByClass(Place::class) as $vertex) {
            yield $vertex->getTitle();
        }
    }

    protected function getNpcTitle(): iterable
    {
        foreach ($this->repository->searchNpcWithToken() as $vertex) {
            yield $vertex->getTitle();
        }
    }

    public function mapDataToForms($viewData, Traversable $forms)
    {
        
    }

    public function mapFormsToData(Traversable $forms, &$viewData)
    {
        $fields = iterator_to_array($forms);
        ob_start();
        echo "==Décor==\n";
        echo '[[' . $fields['place']->getData() . "]]\n";
        echo "==Ambiance==\n";
        echo $fields['ambience']->getData() . PHP_EOL;
        $viewData->setContent(ob_get_clean());
    }

}
