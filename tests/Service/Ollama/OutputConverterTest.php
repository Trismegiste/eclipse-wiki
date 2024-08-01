<?php

/*
 * eclipse-wiki
 */

use App\Service\Ollama\OutputConverter;
use PHPUnit\Framework\TestCase;

class OutputConverterTest extends TestCase
{

    protected OutputConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new OutputConverter();
    }

    public function testWikitext()
    {
        $source = <<<MARKDOWN
coucou

2. **title1** : rien icidfdf fgdfg   
Paraph suppl
**title 2**  
Content paraph with **hilite** youiyou

MARKDOWN;

        $result = $this->sut->toWikitext($source);

        $this->assertStringContainsString('===title1===', $result);
        $this->assertStringContainsString('===title 2===', $result);
        $this->assertStringContainsString("'''hilite'''", $result);
    }

}
