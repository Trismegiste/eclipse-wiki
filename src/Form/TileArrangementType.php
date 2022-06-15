<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\HexagonalTile;
use App\Entity\TileArrangement;
use App\Repository\TileProvider;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of TileArrangementType
 *
 * @author trismegiste
 */
class TileArrangementType extends AbstractType
{

    protected $tileRepo;

    public function __construct(TileProvider $repo)
    {
        $this->tileRepo = $repo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tile = $this->tileRepo->findAll();

        array_walk($tile, function (SplFileInfo &$item) {
            $obj = new HexagonalTile();
            $obj->filename = $item->getRelativePathname();
            $item = $obj;
        });

        $builder
                ->add('title', TextType::class)
                ->add('collection', ChoiceType::class, [
                    'choices' => $tile,
                    'expanded' => true,
                    'multiple' => true,
                    'choice_label' => function ($choice, $key, $value) {
                        return $choice->filename;
                    }
                ])
                ->add('create', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', TileArrangement::class);
    }

}
