<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\PlotNode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of WikiTreeType
 *
 * @author florent
 */
class WikiTreeType extends AbstractType implements DataTransformerInterface
{

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function reverseTransform(mixed $content)
    {
        if (empty($content)) {
            $failure = new TransformationFailedException("Content is empty");
            $failure->setInvalidMessage('Tree is empty');
            throw $failure;
        }

        $data = json_decode($content);
        if (json_last_error() !== 0) {
            $failure = new TransformationFailedException("JSON is not valid");
            $failure->setInvalidMessage('Tree format is not valid');
            throw $failure;
        }

        return $data;
    }

    public function transform(mixed $value)
    {
        $value = new PlotNode('Root', [
            new PlotNode('Act 1', [new PlotNode('Scene 1.1'), new PlotNode('Scene 1.2', [new PlotNode('Event 1.2.1')])]),
            new PlotNode('Act 2'),
            new PlotNode('Act 3', [new PlotNode('Scene 3.1')]),
        ]);

        return json_encode($value);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this);
    }

}
