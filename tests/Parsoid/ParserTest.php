<?php

/*
 * eclipse-wiki
 */

use App\Parsoid\Parser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParserTest extends KernelTestCase
{

    protected Parser $sut;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->sut = static::getContainer()->get(Parser::class);
    }

    public function testOverridenLinksForBrowser()
    {
        $html = $this->sut->parse('[[YOLO with space]]', 'browser');
    }

}
