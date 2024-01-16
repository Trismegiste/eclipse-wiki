<?php

/*
 * Eclipse Wiki
 */

use App\Service\Mercure\SubscriptionClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SubscriptionClientTest extends WebTestCase
{

    protected SubscriptionClient $sut;

    protected function setUp(): void
    {
        $this->sut = self::getContainer()->get(SubscriptionClient::class);
    }

    public function testTopicCompil()
    {
        $topic = $this->sut->getAllTopic();
        $this->assertIsArray($topic);
    }

}
