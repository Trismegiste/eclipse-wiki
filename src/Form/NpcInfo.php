<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Transhuman;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Trismegiste\NameGenerator\FileRepository;

/**
 * Information about a Transhuman
 */
class NpcInfo extends AbstractType
{

    use FormTypeUtils;

    protected $generator;

    public function __construct()
    {
        $this->generator = new FileRepository();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tmpList = $this->generator->getSurnameLanguage();
        sort($tmpList);
        $language['-- Aléatoire --'] = 'random';
        foreach ($tmpList as $lang) {
            $language[ucfirst($lang)] = $lang;
        }


        $builder->add('surnameLang', ChoiceType::class, [
            'choices' => $language,
            'placeholder' => '-------------',
            'required' => false
        ]);
    }

    public function getParent()
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->moveChildAtEnd($view, 'create');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Transhuman::class);
    }

}
