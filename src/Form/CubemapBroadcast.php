<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of CubemapBroadcast
 *
 * @author trismegiste
 */
class CubemapBroadcast extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('picture', CollectionType::class,
                        [
                            'entry_type' => FileType::class,
                            'data' => array_fill(0, 6, null)
                        ])
        ;
    }

}
