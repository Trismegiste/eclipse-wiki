<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Service\Mercure\SubscriptionClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * SChoiceType for topics
 */
class TopicSelectorType extends AbstractType
{

    public function __construct(protected SubscriptionClient $mercure)
    {
        
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver): void
    {
        $topic = $this->mercure->getPrivateTopic();
        array_unshift($topic, 'public');
        $resolver->setDefault('choices', array_combine($topic, $topic));
    }

}