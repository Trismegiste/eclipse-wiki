<?php

/*
 * eclipse-wiki
 */

namespace App\Form\QuickNpc;

use App\Form\NpcCreate;
use App\Service\StableDiffusion\LocalRepository;
use App\Service\Storage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Create a new NPC with the selection of nodes in Creation Graph/Tree
 */
class Selector extends AbstractType
{

    public function __construct(protected LocalRepository $local, protected Storage $storage, protected bool $debugModeEnabled)
    {
        
    }

    public function getParent(): string
    {
        return NpcCreate::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('surnameLang')
                ->remove('content');

        $builder->add('edges', EdgeCheckType::class)
                ->add('attributes', CollectionType::class, [
                    'entry_type' => AttributeHiddenStat::class,
                    'allow_add' => true,
                    'by_reference' => false
                ])
                ->add('skills', CollectionType::class, [
                    'entry_type' => SkillHiddenStat::class,
                    'allow_add' => true,
                    'by_reference' => false
                ])
                ->add('economy', CollectionType::class, [
                    'entry_type' => SocNetHiddenStat::class,
                    'allow_add' => true
                ])
                ->add('content', TextType::class, [
                    'required' => false
                ])
                ->add('node_selection', HiddenType::class, [
                    'attr' => ['x-bind:value' => 'JSON.stringify(choices)'],
                    'mapped' => false
                ])
                ->add('language', \App\Form\Type\SurnameLanguageType::class, [
                    'placeholder' => false,
                    'mapped' => false
                ])
        ;

        $builder->get('economy')->setDataMapper(new SocNetMapper());
        $builder->addViewTransformer(new AppendPictureTranso($this->local, $this->storage, ! $this->debugModeEnabled));
        $builder->addViewTransformer(new AppendHashtagTransfo());
    }

}
