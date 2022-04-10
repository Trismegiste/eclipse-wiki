<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Repository;

use App\Entity\Indexable;
use App\Entity\MediaWikiPage;
use App\Repository\MongoDbProvider;
use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Trismegiste\Strangelove\MongoDb\Repository;

/**
 * Test case for subclass of MongoDbProvider
 */
abstract class MongoDbProviderTestCase extends TestCase
{

    protected $sut;
    protected $repo;

    protected function setUp(): void
    {
        $obj = $this->createPage();
        $this->repo = $this->createMock(Repository::class);
        $this->repo->expects($this->any())
                ->method('search')
                ->with($this->callback(function (array $query) use ($obj) {
                            return array_key_exists('category', $query) && $query['category'] === $obj->getCategory();
                        }))
                ->willReturn(new ArrayIterator([$obj]));

        $this->sut = $this->createProvider($this->repo);
    }

    public function testFindOne()
    {
        $obj = $this->sut->findOne('Dummy');
        $this->assertEquals('Dummy', $obj->getName());
    }

    public function testFindAll()
    {
        $obj = $this->sut->getListing();
        $this->assertCount(1, $obj);
        $this->assertArrayHasKey('Dummy', $obj);
        $this->assertInstanceOf(Indexable::class, $obj['Dummy']);
        $this->assertEquals('Dummy', $obj['Dummy']->getName());
        $this->assertDetail($obj['Dummy']);
    }

    abstract protected function createPage(): MediaWikiPage;

    abstract protected function createProvider(Repository $repo): MongoDbProvider;

    abstract protected function assertDetail(Indexable $obj);
}
