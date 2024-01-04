<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\CreationDag;

use App\Entity\CreationTree\Graph;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of TreeLevelEdit
 *
 * @author florent
 */
class TreeLevelEdit extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Graph $graph */
        $graph = $options['data'];
        $root = $graph->getNodeByName('root');
        foreach ($graph->node as $idx => $node) {
            $dst = $graph->getShortestDistanceFromAncestor($node, $root);
            if ($dst === $options['level']) {
                $builder->add("property_$idx", AttributeBonus::class, [
                    'required' => false,
                    'property_path' => "node[$idx].attributes",
                    'label' => $node->name
                ]);
            }
        }

        $builder->add('save', SubmitType::class)
                ->setMethod('PUT')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Graph::class,
        ]);
        $resolver->setRequired(['level', 'property_name']);
    }

}
