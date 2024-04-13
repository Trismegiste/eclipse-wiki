<?php

/*
 * Eclipse Wiki
 */

namespace App\Tests\Parsoid\TagHandler;

use App\Parsoid\Parser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Root test case class for testing TagHandler extension
 */
class ExtensionTestCase extends KernelTestCase
{

    protected Parser $parser;

    public function getTarget()
    {
        return [
            ['browser'],
            ['pdf']
        ];
    }

    protected function setUp(): void
    {
        $this->parser = self::getContainer()->get(Parser::class);
    }

}
