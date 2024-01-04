<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\CreationDag;

use App\Entity\CreationTree\Graph;
use App\Repository\BackgroundProvider;
use App\Repository\FactionProvider;
use App\Repository\MorphProvider;
use Symfony\Component\Form\AbstractType;
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

    public function __construct(protected BackgroundProvider $background, protected FactionProvider $faction, protected MorphProvider $morph)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Graph $graph */
        $graph = $options['data'];
        $root = $graph->getNodeByName('root');
        foreach ($graph->node as $idx => $node) {
            $dst = $graph->getShortestDistanceFromAncestor($node, $root);
            if ($dst === $options['level']) {
                $childOptions = [
                    'required' => false,
                    'property_path' => "node[$idx]." . $options['property_name'],
                    'label' => $node->name,
                    'attr' => ['size' => 12]
                ];
                switch ($options['property_name']) {
                    case 'attributes':
                        $fqcn = AttributeBonus::class;
                        break;
                    case 'skills':
                        $fqcn = SkillBonus::class;
                        break;
                    case 'edges':
                        $fqcn = EdgeSelection::class;
                        break;
                    case 'networks':
                        $fqcn = NetworkBonus::class;
                        break;
                    case 'backgrounds':
                        $fqcn = \App\Form\Type\MultiCheckboxType::class;
                        $childOptions['choices'] = $this->background->getListing();
                        break;
                    case 'factions':
                        $fqcn = \App\Form\Type\MultiCheckboxType::class;
                        $childOptions['choices'] = $this->faction->getListing();
                        break;
                    case 'morphs':
                        $fqcn = \App\Form\Type\MultiCheckboxType::class;
                        $childOptions['choices'] = $this->morph->getListing();
                        break;
                }
                $builder->add("property_$idx", $fqcn, $childOptions);
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
