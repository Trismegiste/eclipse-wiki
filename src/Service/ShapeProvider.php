<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Shape\Border;
use App\Entity\Shape\Dome;
use App\Entity\Shape\Hexadome;
use App\Entity\Shape\NullShape;
use App\Entity\Shape\Starship;
use App\Entity\Shape\SvgStrategy;
use App\Entity\Shape\Torus;
use Symfony\Component\Finder\Finder;

/**
 * Repository for Shape Strategies
 */
class ShapeProvider
{

    protected string $path;

    public function __construct(string $basepath)
    {
        $this->path = $basepath;
    }

    /**
     * Gets all Shape Strategies
     */
    public function findAll(): array
    {
        // default shapes
        $listing = [
            new NullShape(),
            new Border(),
            new Dome(),
            new Torus(),
            new Starship(),
            new Hexadome()
        ];

        // Appends all SVG in shapes folder to create dynamic strategy
	$finder = new Finder();
        $finder->in($this->path)->files()->name('*.svg');

        foreach ($finder as $file) {
            $listing[] = new SvgStrategy($file->getBasename('.svg'), $file->getContents());
        }

        return $listing;
    }

}
