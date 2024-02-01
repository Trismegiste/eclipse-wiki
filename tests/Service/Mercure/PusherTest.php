<?php

/*
 * eclipse-wiki
 */

use App\Service\Mercure\Pusher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;

class PusherTest extends TestCase
{

    use \App\Tests\Controller\PictureFixture;

    protected Pusher $sut;
    protected HubInterface&MockObject $hub;

    protected function setUp(): void
    {
        $this->hub = $this->createMock(HubInterface::class);
        $this->hub->expects($this->once())
                ->method('publish');

        $this->sut = new Pusher($this->hub);
    }

    public function testValidPeering()
    {
        $this->sut->validPeering(666, 'Takeshi');
    }

    public function testSendDocumentLink()
    {
        $this->sut->sendDocumentLink('http://192.168.68.27/getdoc/yolo/pdf', 'Handout Yolo');
    }

    public function testPicture()
    {
        $folder = __DIR__ . '/../../fixtures';
        $img = join_paths($folder, App\Tests\Service\StableDiffusion\PngReaderTest::fixture);
        $this->sut->sendPictureAsDataUrl(new SplFileInfo($img), 'picture');
    }

}
