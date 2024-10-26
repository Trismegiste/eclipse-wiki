<?php

/*
 * eclipse-wiki
 */

use App\Entity\Handout;
use App\Entity\Scene;
use App\Parsoid\Parser;
use App\Repository\VertexRepository;
use App\Service\Storage;
use App\Tests\Controller\PictureFixture;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Trismegiste\Strangelove\MongoDb\Repository;

class ParserTest extends KernelTestCase
{

    use PictureFixture;

    protected Parser $sut;
    protected Crawler $crawler;
    protected UrlGeneratorInterface $routing;
    protected Repository $repo;
    protected Storage $storage;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->sut = static::getContainer()->get(Parser::class);
        $this->routing = static::getContainer()->get(UrlGeneratorInterface::class);
        $this->crawler = new Crawler();
        $this->repo = static::getContainer()->get(VertexRepository::class);
        $this->storage = static::getContainer()->get(Storage::class);
    }

    public function testClean()
    {
        $this->repo->delete(iterator_to_array($this->repo->search()));
        $this->assertCount(0, iterator_to_array($this->repo->search()));
    }

    public function testUnknownOverridenLinksForBrowser()
    {
        $html = $this->sut->parse('[[YOLO with space]]', 'browser');
        $this->crawler->addHtmlContent($html);
        // Why assertStringStartsWith and not assertEquals ?
        // Because, somewhere in Parsoid, a "?action=edit&redlink" is appended after the link
        $this->assertStringStartsWith($this->routing->generate('app_wiki', ['title' => 'YOLO with space']), $this->crawler->filter('a')->attr('href'));
    }

    public function testKnownOverridenLinksForBrowser()
    {
        $scene = new Scene('scene with space');
        $this->repo->save($scene);

        $html = $this->sut->parse('[[scene with space]]', 'browser');
        $this->crawler->addHtmlContent($html);
        $this->assertEquals($this->routing->generate('app_wiki', ['title' => 'scene with space']), $this->crawler->filter('a')->attr('href'));
    }

    public function testOverridenLinksForPdf()
    {
        $v = new Handout('handout with àççéèêëàôöts');
        $v->pcInfo = 'something';
        $this->repo->save($v);

        $html = $this->sut->parse('[[handout with àççéèêëàôöts]]', 'pdf');
        $this->crawler->addHtmlContent($html);
        $this->assertEquals('', $this->crawler->filter('a')->attr('href'));
    }

    public function testPictureForBrowser()
    {
        $src = $this->createTestChart(256);
        $target = new SplFileInfo(join_paths($this->storage->getRootDir(), 'wikipic.png'));
        imagepng($src, $target->getPathname());

        $html = $this->sut->parse('[[file:wikipic.png]]', 'browser');
        $this->crawler->addHtmlContent($html);
        $this->assertEquals($this->routing->generate('get_picture', ['title' => 'wikipic.png']), $this->crawler->filter('img')->attr('src'));
    }

    /** @depends testPictureForBrowser */
    public function testPictureForPdf()
    {
        $html = $this->sut->parse('[[file:wikipic.png]]', 'pdf');
        $this->crawler->addHtmlContent($html);
        $src = $this->crawler->filter('img')->attr('src');
        $this->assertStringStartsWith('file:///', $src);
        unlink($src);
    }

    public function testMissingPictureForBrowser()
    {
        $html = $this->sut->parse('[[file:cthulhu.jpg]]', 'browser');
        $this->crawler->addHtmlContent($html);
        $this->assertEquals($this->routing->generate('app_picture_uploadmissing', ['title' => 'cthulhu.jpg', 'redirect' => '/']), $this->crawler->filter('a')->attr('href'));
    }

    public function testTemplateEngine()
    {
        $html = $this->sut->parse('{{legend|dynamic|123}}', 'browser');
        $this->assertStringContainsString('dynamic', $html);
        $this->crawler->addHtmlContent($html);
        $this->assertCount(1, $this->crawler->filter('i[data-cell-index=123]'));
    }

    public function testExtractTag()
    {
        $wikitext = 'outside<tag title="yolo">content</tag>outside';
        $filtered = $this->sut->extractTagContent($wikitext, 'tag', 'yolo');
        $this->assertEquals('<tag title="yolo">content</tag>', $filtered);
    }

    public function testBugDoubleLinksFailWithUcFirst()
    {
        $scene = new Scene('First Page');
        $this->repo->save($scene);

        $html = $this->sut->parse('[[First Page]] [[first Page]] [[first page]]', 'browser');
        $this->crawler->addHtmlContent($html);
        $this->assertCount(3, $this->crawler->filter('a'));

        $this->assertNull($this->crawler->filter('a')->eq(1)->attr('class'), 'Second existing link is not classless');
        $this->assertEquals('new', $this->crawler->filter('a')->eq(2)->attr('class'), 'Last missing link is not red');
        $this->assertNull($this->crawler->filter('a')->eq(0)->attr('class'), 'First existing link is not classless');
    }

}
