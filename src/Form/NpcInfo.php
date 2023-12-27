<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Transhuman;
use App\Form\Type\HashtagType;
use App\Form\Type\SurnameLanguageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Information about a Transhuman
 */
class NpcInfo extends AbstractType
{

    use FormTypeUtils;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('hashtag', HashtagType::class, ['required' => false, 'default_hashtag' => $options['data']->getDefaultHashtag()])
                ->add('surnameLang', SurnameLanguageType::class)
                ->add('tokenPicPrompt', TextType::class, ['required' => false]);
    }

    public function getParent(): ?string
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $this->moveChildAtEnd($view, 'create');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Transhuman::class);
    }

}
