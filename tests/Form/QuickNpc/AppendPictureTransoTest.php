<?php

/*
 * eclipse-wiki
 */

use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Transhuman;
use App\Form\QuickNpc\AppendPictureTranso;
use App\Service\StableDiffusion\PictureRepository;
use App\Service\Storage;
use PHPUnit\Framework\TestCase;

class AppendPictureTransoTest extends TestCase
{

    use App\Tests\Controller\PictureFixture;

    public function testUploadFromInvokeAi()
    {
        $tmpImg = tmpfile();
        $pathname = stream_get_meta_data($tmpImg)['uri'];
        $img = $this->createTestChart(256);
        imagepng($img, $pathname);

        $source = $this->getMockForAbstractClass(PictureRepository::class);
        $source->expects($this->atLeastOnce())
                ->method('getAbsoluteUrl')
                ->willReturn($pathname);

        $target = $this->createMock(Storage::class);
        $transfo = new AppendPictureTranso($source, $target);

        $npc = new Transhuman('alice', $this->createStub(Background::class), $this->createStub(Faction::class));
        $this->assertEquals('Alice', $npc->getTitle());
        $npc->setContent('picture_name');

        $transfo->reverseTransform($npc);

        $this->assertStringStartsWith("[[file:Alice-", $npc->getContent());
        $this->assertStringEndsWith(".jpg]]", $npc->getContent());
        $this->assertStringStartsWith('token-', $npc->tokenPic);
        $this->assertStringEndsWith('.png', $npc->tokenPic);
        unlink($npc->tokenPic);
    }

}
