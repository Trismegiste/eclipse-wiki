<?php

/*
 * Eclipse Wiki
 */

use App\Tests\Parsoid\TagHandler\ExtensionTestCase;

class PushPublicTest extends ExtensionTestCase
{

    /** @dataProvider getTarget */
    public function testParsing(string $target)
    {
        $wikitext = <<<WIKITEXT
<pushpublic>
King Arthur
</pushpublic>
WIKITEXT;
        $html = $this->parser->parse($wikitext, $target);
        $this->assertStringContainsString('icon', $html);
        $this->assertStringContainsString('<blockquote', $html);
        $this->assertStringContainsString('data-pushable="pdf"', $html);
        $this->assertStringContainsString('Arthur', $html);
    }

}
