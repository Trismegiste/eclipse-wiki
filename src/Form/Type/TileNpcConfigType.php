<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\TileNpcConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;
use Traversable;

/**
 * NPC config for each tile
 */
class TileNpcConfigType extends AbstractType implements DataMapperInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('npcTitle', NpcChoiceType::class, [
                    'placeholder' => '-----------',
                    'required' => false
                ])
                ->add('tilePerNpc', IntegerType::class, [
                    'constraints' => [new Positive()],
                    'required' => false,
                    'error_bubbling' => true
        ]);
        $builder->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => TileNpcConfig::class]);
    }

    public function mapDataToForms($viewData, Traversable $forms)
    {
        if (is_null($viewData)) {
            return;
        }

        $forms = iterator_to_array($forms);
        $forms['npcTitle']->setData($viewData->npcTitle);
        $forms['tilePerNpc']->setData($viewData->tilePerNpc);
    }

    public function mapFormsToData(Traversable $forms, &$viewData)
    {
        $forms = iterator_to_array($forms);
        if (!empty($forms['tilePerNpc']->getData()) && !empty($forms['npcTitle']->getData())) {
            $viewData->tilePerNpc = $forms['tilePerNpc']->getData();
            $viewData->npcTitle = $forms['npcTitle']->getData();
        } else {
            $viewData = null;
        }
    }

}
