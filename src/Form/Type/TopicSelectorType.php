<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Service\Mercure\SubscriptionClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * SChoiceType for topics
 */
class TopicSelectorType extends AbstractType
{

    const PUBLIC_CHANNEL = 'public';

    public function __construct(protected SubscriptionClient $mercure)
    {
        
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $topic = $this->mercure->getPrivateTopic();
        array_unshift($topic, self::PUBLIC_CHANNEL);
        $resolver->setDefault('choices', array_combine($topic, $topic));
    }

}
