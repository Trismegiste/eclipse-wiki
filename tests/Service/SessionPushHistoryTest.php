<?php

/*
 * eclipse-wiki
 */

use App\Service\SessionPushHistory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SessionPushHistoryTest extends WebTestCase
{

    protected SessionPushHistory $sut;
    protected string $cacheDir;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(SessionPushHistory::class);
        $this->cacheDir = static::getContainer()->getParameter('kernel.cache_dir');
    }

    public function testClear()
    {
        $this->sut->clear($this->cacheDir);
        $it = (new Finder())
                ->in(join_paths($this->cacheDir, SessionPushHistory::subDir))
                ->files();
        $this->assertCount(0, $it);
    }

    public function testNotFoundFile()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->sut->getFileInfo('rickroll.gif');
    }

}
