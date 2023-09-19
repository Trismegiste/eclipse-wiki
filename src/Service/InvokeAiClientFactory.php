<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use DateInterval;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Process\Process;

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
	return $this->createFromHostname($this->findIpFromMac($mac));
    }

    protected function findIpFromMac(string $mac): string
    {
        $arp = new Process(['arp', '-n']);
        $arp->mustRun();

        $rows = explode("\n", $arp->getOutput());
        foreach($rows as $row) {
            if (str_contains($row, $mac)) {
                if (preg_match('#^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s#', $row, $extract)) {
                    return $extract[1];
                }
            }
        }

        throw new \RuntimeException('Unknown MAC address');
    }

}
