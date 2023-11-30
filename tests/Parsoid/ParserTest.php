<?php

/*
 * eclipse-wiki
 */

use App\Parsoid\Parser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ParserTest extends WebTestCase
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
        $crawler = new Crawler($html);

        echo $crawler->filter('a')->attr('href');
    }

}
