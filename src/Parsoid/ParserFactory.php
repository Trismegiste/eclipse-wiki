<?php

/*
 * eclipse-wiki
 */

namespace App\Parsoid;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wikimedia\Parsoid\Parsoid;

/**
 * Creates Parser for different targets
 * Design Pattern : Multiton
 */
class ParserFactory
{

    protected array $instance = [];
    protected array $extension;

    public function __construct(protected InternalDataAccess $access, UrlGeneratorInterface $router)
    {
        $this->extension = [
            'browser' => [
                'class' => SymfonyBridge::class,
                'args' => [$router]
            ]
        ];
    }

    public function create(string $target): Parsoid
    {
        if (key_exists($target, $this->instance)) {
            return $this->instance[$target];
        }
        if (!key_exists($target, $this->extension)) {
            throw new \InvalidArgumentException("'$target' is not a valid target for wikitext rendering. "
                            . "Current configurations avaliable are: [" . implode(', ', array_keys($this->extension)) . ']');
        }

        $siteConfig = new InternalSiteConfig();
        $siteConfig->registerExtensionModule($this->extension[$target]);

        $this->instance[$target] = new Parsoid($siteConfig, $this->access);

        return $this->instance[$target];
    }

}
