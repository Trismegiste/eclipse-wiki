<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Service\Storage;
use Symfony\Component\Form\DataTransformerInterface;
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
        $filename = 'map3d-' . $this->uniqueId . '.json';
        file_put_contents(join_paths($this->storage->getRootDir(), $filename), $content);

        return $filename;
    }

    public function transform($value): string
    {
        return '';
    }

}
