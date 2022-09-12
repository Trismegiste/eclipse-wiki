<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\MapConfig;
use App\Repository\TileProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

/**
 * Form for texturing a map
 */
class MapTextureType extends AbstractType implements DataMapperInterface
{

    protected TileProvider $provider;

    public function __construct(TileProvider $provider)
    {
        $this->provider = $provider;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', MapConfig::class);
        $resolver->setRequired('tileset');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->provider->getClusterSet($options['tileset']) as $tile) {
            preg_match("#^cluster-([a-z]+)$#", $tile->getKey(), $match);
            $builder->add($tile->getKey(), IntegerType::class, ['required' => false, 'label' => ucfirst($match[1])]);
        }
        $builder->setDataMapper($this);
    }

    public function mapDataToForms($viewData, Traversable $forms)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!is_array($viewData->tileWeight)) {
            throw new UnexpectedTypeException($viewData->tileWeight, 'array');
        }

        /** @var FormInterface $field */
        foreach ($forms as $key => $field) {
            if (key_exists($key, $viewData->tileWeight)) {
                $field->setData($viewData->tileWeight[$key]);
            }
        }
    }

    public function mapFormsToData(Traversable $forms, &$viewData)
    {
        /** @var MapConfig $viewData */
        $viewData->tileWeight = [];
        /** @var FormInterface $field */
        foreach ($forms as $key => $field) {
            $val = $field->getData();
            if (!empty($val)) {
                $viewData->tileWeight[$key] = $val;
            }
        }
    }

}
