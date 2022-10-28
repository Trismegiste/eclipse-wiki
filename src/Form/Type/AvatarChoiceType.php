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
 * Description of AvatarChoiceType
 *
 * @author florent
 */
class AvatarChoiceType extends AbstractType
{

    protected $bauhausAvatar;

    public function __construct(BoringAvatar $maker)
    {
        $this->bauhausAvatar = $maker;
    }

    public function getParent()
    {
        return FileType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $bauhaus = [];
        for ($k = 0; $k < $options['abstract_number']; $k++) {
            $bauhaus[] = $this->bauhausAvatar->createBauhaus('yolo' . rand());
        }
        $view->vars['bauhaus'] = $bauhaus;
        $view->vars['avatar_size'] = $options['avatar_size'];
        $view->vars['default_bitmap'] = $options['bitmap'];
        $view->vars['human_number'] = $options['human_number'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'bitmap' => null,
            'avatar_size' => 503,
            'abstract_number' => 5,
            'human_number' => 6
        ]);
    }

}
