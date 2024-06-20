<?php

/*
 * eclipse-wiki
 */

use App\Algebra\GraphVertex;
use App\Entity\Place;
use App\Service\PartitionGalleryFactory;
use App\Tests\Controller\PictureFixture;
use MongoDB\BSON\ObjectId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\TwigFunction;

class PartitionGalleryFactoryTest extends TestCase
{

    use PictureFixture;

    protected PartitionGalleryFactory $sut;

    protected function setUp(): void
    {
        $routing = $this->createMock(UrlGeneratorInterface::class);

        $twig = $this->createMock(Environment::class);
        $iconExt = new TwigFunction('vertex_icon', function () {
                    return 'icon';
                });
        $twig->expects($this->any())
                ->method('getFunction')
                ->willReturn($iconExt);

        $this->sut = new PartitionGalleryFactory($routing, $twig);
    }

    public function testPerCategory()
    {
        $npc1 = $this->createRandomTranshuman();
        $npc1->setContent('[[file:npc.jpg]]');

        $npc2 = $this->createRandomTranshuman();
        $npc2->tokenPic = 'yolo.png';

        $cursor = new ArrayIterator([
            $this->createRandomScene(),
            $npc1,
            $npc2
        ]);
        foreach ($cursor as $v) {
            $v->setPk(new ObjectId());
        }

        $gallery = $this->sut->createGalleryPerCategory($cursor);
        $this->assertCount(2, $gallery);
        $this->assertArrayHasKey('scene', $gallery);
        $this->assertCount(1, $gallery['scene']);
        $this->assertCount(2, $gallery['transhuman']);
    }

    public function testPoster()
    {
        $item1 = new GraphVertex(['_id' => '1234', 'title' => 'Essai1', '__pclass' => Place::class]);
        $item1->betweenness = 1;
        $item1->picture = ['yolo1.png'];

        $item2 = new GraphVertex(['_id' => '1234', 'title' => 'Essai2', '__pclass' => Place::class]);
        $item2->betweenness = 1;
        $item2->picture = ['yolo2.png'];

        $item3 = new GraphVertex(['_id' => '1234', 'title' => 'Essai3', '__pclass' => Place::class]);
        $item3->betweenness = 1;
        $item3->picture = ['yolo2.png']; // same picture

        $gallery = $this->sut->createMoviePoster([$item1, $item2, $item3]);
        $this->assertCount(2, $gallery);
        $this->assertEquals('yolo2.png', $gallery[0]);
        $this->assertEquals('yolo1.png', $gallery[1]);
    }

}
