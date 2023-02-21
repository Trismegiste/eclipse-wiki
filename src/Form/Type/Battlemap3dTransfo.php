<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Service\Storage;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use function join_paths;

/**
 * Transfo text to file
 */
class Battlemap3dTransfo implements DataTransformerInterface
{

    protected Storage $storage;
    protected string $uniqueId;

    public function __construct(Storage $storage, string $uniqueId)
    {
        $this->storage = $storage;
        $this->uniqueId = $uniqueId;
    }

    public function reverseTransform($content): string
    {
        if (empty($content)) {
            $failure = new TransformationFailedException("Content is empty");
            $failure->setInvalidMessage('Battlemap is empty');
            throw $failure;
        }

        json_decode($content);
        if (json_last_error() !== 0) {
            $failure = new TransformationFailedException("JSON is not valid");
            $failure->setInvalidMessage('Battlemap format is not valid');
            throw $failure;
        }

        $filename = 'map3d-' . $this->uniqueId . '.json';
        file_put_contents(join_paths($this->storage->getRootDir(), $filename), $content);

        return $filename;
    }

    public function transform($value): string
    {
        return '';
    }

}
