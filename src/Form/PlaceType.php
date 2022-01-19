<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Place;
use App\Entity\Transhuman;
use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Trismegiste\NameGenerator\FileRepository;

/**
 * Form for creating and editing a Place
 */
class PlaceType extends AbstractType
{

    use FormTypeUtils;

    protected $repository;
    protected $generator;

    public function __construct(VertexRepository $repository)
    {
        $this->repository = $repository;
        $this->generator = new FileRepository();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tmpList = $this->generator->getSurnameLanguage();
        sort($tmpList);
        $language['-- Aléatoire --'] = 'random';
        foreach ($tmpList as $lang) {
            $language[ucfirst($lang)] = $lang;
        }

        $npcList = [];
        foreach ($this->repository->findByClass(Transhuman::class) as $npc) {
            if (!$npc->wildCard) {
                $npcList[$npc->getTitle()] = $npc->getTitle();
            }
        }

        $builder
                ->add('gravity', Type\FullTextChoice::class, ['category' => 'gravity'])
                ->add('temperature', Type\FullTextChoice::class, ['category' => 'temperature'])
                ->add('pressure', Type\FullTextChoice::class, ['category' => 'pressure'])
                ->add('youtubeUrl', Type\YoutubeType::class, [
                    'required' => false,
                    'label' => 'Youtube ID',
                    'attr' => [
                        'class' => 'pure-input-1-2',
                        'placeholder' => 'ID unique de Youtube ou url de la vidéo'
                    ]
                ])
                ->add('surnameLang', ChoiceType::class, [
                    'choices' => $language
                ])
                ->add('npcTemplate', ChoiceType::class, [
                    'required' => false,
                    'placeholder' => '----------------',
                    'choices' => $npcList
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Place($form->get('title')->getData());
        });
    }

    public function getParent()
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->changeAttribute($view, 'content', 'rows', 24);
        $this->moveChildAtEnd($view, 'content');
        $this->moveChildAtEnd($view, 'create');
    }

}
