<?php

/*
 * Eclipse Wiki
 */

use App\Service\Storage;
use App\Tests\Controller\PictureFixture;
use App\Tests\Parsoid\TagHandler\ExtensionTestCase;

class CarrouselTest extends ExtensionTestCase
{

    use PictureFixture;

    /** @dataProvider getTarget */
    public function testParsing(string $target)
    {
        $gd = $this->createTestChart(200);
        $storage = self::getContainer()->get(Storage::class);
        $img = join_paths($storage->getRootDir(), 'carrousel.jpg');
        imagejpeg($gd, $img);

        $html = $this->parser->parse("<carrousel>[[file:carrousel.jpg]]\nyolo</carrousel>", $target);
        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('<a href', $html);
    }

}
