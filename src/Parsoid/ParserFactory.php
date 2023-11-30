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

    protected $instance = null;

    public function __construct(protected InternalDataAccess $access, protected UrlGeneratorInterface $router)
    {
        
    }

    public function create(string $target): Parsoid
    {
        if (!is_null($this->instance)) {
            return $this->instance;
        }

        $siteConfig = new InternalSiteConfig();
        $siteConfig->registerExtensionModule([
            'class' => SymfonyBridge::class,
            'args' => [$this->router]
        ]);

        $this->instance = new Parsoid($siteConfig, $this->access);

        return $this->instance;
    }

}
