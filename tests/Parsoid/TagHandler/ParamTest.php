<?php

/*
 * Eclipse Wiki
 */

use App\Tests\Parsoid\TagHandler\ExtensionTestCase;

class ParamTest extends ExtensionTestCase
{

    /** @dataProvider getTarget */
    public function testParsing(string $target)
    {
        $wikitext = <<<WIKITEXT
<param>
population: 12000
</param>
WIKITEXT;
        $html = $this->parser->parse($wikitext, $target);
        $this->assertStringContainsString('population', $html);
        $this->assertStringContainsString('12000', $html);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('<th', $html);
        $this->assertStringContainsString('<td', $html);
    }

    /** @dataProvider getTarget */
    public function testParsingBadEntry(string $target)
    {
        $wikitext = <<<WIKITEXT
<param>
population 12000
</param>
WIKITEXT;
        $html = $this->parser->parse($wikitext, $target);
        $this->assertStringNotContainsString('<tr>', $html);
    }

}
