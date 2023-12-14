<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Service\BoringAvatar;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Generates avatar with multiple services
 */
class AvatarChoiceType extends AbstractType
{

    protected $bauhausAvatar;

    public function __construct(BoringAvatar $maker)
    {
        $this->bauhausAvatar = $maker;
    }

    public function getParent(): ?string
    {
        return FileType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $bauhaus = [];
        for ($k = 0; $k < $options['abstract_number']; $k++) {
            $bauhaus[] = $this->bauhausAvatar->createBauhaus('yolo' . rand());
        }
        $view->vars['bauhaus'] = $bauhaus;
        $view->vars['avatar_size'] = $options['avatar_size'];
        $view->vars['default_bitmap'] = $options['bitmap'];
        $view->vars['human_number'] = $options['human_number'] + (empty($options['bitmap']) ? 1 : 0);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'bitmap' => null,
            'avatar_size' => 503,
            'abstract_number' => 6,
            'human_number' => 29
        ]);
    }

}
