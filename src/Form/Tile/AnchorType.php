<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Tile;

use App\Entity\HexagonalTile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for editing anchors on a tile
 */
class AnchorType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('anchor', CollectionType::class, [
                    'entry_type' => TextType::class
        ]);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', HexagonalTile::class);
    }

}
