<?php

/*
 * Eclipse Wiki
 */

use App\Tests\Parsoid\TagHandler\ExtensionTestCase;

class MorphBankTest extends ExtensionTestCase
{

    /** @dataProvider getTarget */
    public function testParsing(string $target)
    {
        $wikitext = <<<WIKITEXT
<morphbank title="yolo">
Huldre|3|44
</morphbank>
WIKITEXT;
        $html = $this->parser->parse($wikitext, $target);
        $this->assertStringContainsString('<caption', $html);
        $this->assertStringContainsString('yolo', $html);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('Huldre', $html);
        $this->assertStringContainsString('44', $html);
        $this->assertStringContainsString('data-pushable="pdf"', $html);
        $this->assertStringContainsString('icon', $html);
    }

}
