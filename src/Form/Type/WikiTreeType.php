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
 * Tree editor
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

        return $this->backtrackCreateNode($data);
    }

    protected function backtrackCreateNode(\stdClass $node): PlotNode
    {
        $tree = new PlotNode(title: $node->data->title, finished: $node->data->finished);
        foreach ($node->nodes as $child) {
            $tree->nodes[] = $this->backtrackCreateNode($child);
        }

        return $tree;
    }

    public function transform(mixed $value)
    {
        if (empty($value)) {
            $value = new PlotNode('Root');
        }

        return json_encode($value);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this);
    }

}
