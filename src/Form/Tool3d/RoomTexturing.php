<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Tool3d;

use App\Repository\TileProvider;
use App\Voronoi\TileSvg;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Tool pour painting room with a texture
 */
class RoomTexturing extends AbstractType
{

    public function __construct(protected TileProvider $provider)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('texture', ChoiceType::class, [
                    'choices' => $this->provider->getTileSet('habitat'),
                    'attr' => [
                        'x-model' => 'cellInfo.template',
                        'x-on:focus' => "\$el.querySelectorAll('option').forEach(opt => { if (!scene.metadata.texture.includes(opt.value)) {opt.hidden=true} })"
                    ],
                    'choice_value' => function (?TileSvg $obj) {
                        return $obj?->getKey();
                    },
                    'choice_label' => function (?TileSvg $obj) {
                        return $obj?->getKey();
                    }
                ])
                ->add('paint', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('attr', ['x-on:submit' => "texturing"]);
    }

}
