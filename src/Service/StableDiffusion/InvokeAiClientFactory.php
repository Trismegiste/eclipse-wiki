<?php

/*
 * Eclipse Wiki
 */

namespace App\Service\StableDiffusion;

use DateInterval;
use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function str_contains;

/**
 * Factory for stable diffusion client
 */
class InvokeAiClientFactory
{

    public function __construct(protected HttpClientInterface $client,
            protected CacheInterface $invokeaiCache,
            protected int $port = 9090,
            protected string $protocol = 'http')
    {
        
    }

    public function createFromHostname(string $hostname): InvokeAi
    {
        $baseUrl = $this->protocol . '://' . $hostname . ':' . $this->port . '/';

        return new InvokeAi($this->client, $baseUrl, $this->invokeaiCache);
    }

    public function createFromMac(string $mac): InvokeAi
    {
        try {
            $ip = $this->findIpFromMac($mac);
        } catch (RuntimeException $e) {
            $ip = '127.0.0.1';
        }

        return $this->createFromHostname($ip);
    }

    protected function findIpFromMac(string $mac): string
    {
        return $this->invokeaiCache->get("invokeai-hostname", function (ItemInterface $item) use ($mac): string {
                    $item->expiresAfter(DateInterval::createFromDateString('5 minute'));
                    $arp = new Process(['arp', '-n']);
                    $arp->mustRun();

                    $rows = explode("\n", $arp->getOutput());
                    foreach ($rows as $row) {
                        if (str_contains($row, $mac)) {
                            if (preg_match('#^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s#', $row, $extract)) {
                                return $extract[1];
                            }
                        }
                    }

                    throw new RuntimeException('Unknown MAC address');
                });
    }

}
