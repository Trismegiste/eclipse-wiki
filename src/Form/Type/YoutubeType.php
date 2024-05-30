<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * a Youtube primary key (the unique ID after the « youtube.com/watch?v= »
 */
class YoutubeType extends AbstractType implements DataTransformerInterface
{

    const YOUTUBE_ID = '#^([-_a-zA-Z\d]{11})$#';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer($this);
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function reverseTransform($value): mixed
    {
        if (empty($value)) {
            return null;
        }

        // only the ID is sent
        if (preg_match(self::YOUTUBE_ID, $value, $extract)) {
            return $extract[1];
        }

        // test if URL
        $url = parse_url($value);
        if (false === $url) {
            throw new TransformationFailedException("Malformed URL");
        }

        // Is there a hostname ?
        if (!key_exists('host', $url)) {
            throw new TransformationFailedException("Bad hostname");
        }

        // Is this one of youtube hostnames ?
        if (!in_array($url['host'], ['youtu.be', 'www.youtube.com', 'youtube.com'])) {
            throw new TransformationFailedException("Hostname is not Youtube");
        }

        switch ($url['host']) {
            case 'youtube.com':
            case 'www.youtube.com':
                parse_str($url['query'], $parameters);
                if (!key_exists('v', $parameters)) {
                    throw new TransformationFailedException("ID of the video is missing");
                }
                if (!preg_match(self::YOUTUBE_ID, $parameters['v'])) {
                    throw new TransformationFailedException("Video ID is not valid");
                }
                return $parameters['v'];

            case 'youtu.be':
                if (preg_match('#^/([-_a-zA-Z\d]{11})$#', $url['path'], $extract)) {
                    return $extract[1];
                } else {
                    throw new TransformationFailedException("Incomplete shortened URL");
                }
        }

        throw new TransformationFailedException("Unable to find the ID of the video");
    }

    public function transform($value): mixed
    {
        return $value;
    }

}
