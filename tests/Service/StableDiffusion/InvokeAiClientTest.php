<?php

/*
 * eclipse-wiki
 */

use App\Service\StableDiffusion\InvokeAiClient;
use App\Service\StableDiffusion\PictureInfo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class InvokeAiClientTest extends KernelTestCase
{

    protected InvokeAiClient $sut;

    protected function setUp(): void
    {
        $this->sut = self::getContainer()->get(InvokeAiClient::class);
    }

    public function testSearch()
    {
        try {
            $listing = $this->sut->searchPicture('male');
            $this->assertIsArray($listing);
            return $listing;
        } catch (TimeoutException $e) {
            $this->markTestSkipped('InvokeAI server is unreachable');
        }
    }

    /** @depends testSearch */
    public function testSearchResult(array $iter)
    {
        $this->assertGreaterThanOrEqual(1, count($iter), "The 'Uncategorized' category on the InvokeAI server does not contain at least one picture generated with the 'male' keyword in the positive prompt");
        $this->assertInstanceOf(PictureInfo::class, $iter[0]);

        return $iter[0];
    }

    /** @depends testSearchResult */
    public function testFoundPicture(PictureInfo $pic)
    {
        $this->assertStringContainsString('male', $pic->prompt);

        $client = self::getContainer()->get(HttpClientInterface::class);

        // test thumbnail
        $thumb = $client->request('GET', $pic->thumb);
        $this->assertImageResponse($thumb, 'Thumbnail');
        // test full format
        $full = $client->request('GET', $pic->full);
        $this->assertImageResponse($full, 'Full size picture');
    }

    public function assertImageResponse(ResponseInterface $resp, string $message = 'Picture')
    {
        $this->assertEquals(200, $resp->getStatusCode(), "$message is unreacheable");
        $mimeType = $resp->getHeaders()['content-type'][0];
        $this->assertStringStartsWith('image/', $mimeType, "$message is not of image type ($mimeType instead)");
        $picture = imagecreatefromstring($resp->getContent());
        $this->assertNotFalse($picture, "$message is an invalid picture");
        imagedestroy($picture);
    }

}
