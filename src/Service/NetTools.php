<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use DateInterval;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Some tools for Networking
 */
class NetTools
{

    protected $cache;
    protected $urlGenerator;

    public function __construct(CacheInterface $cache, UrlGeneratorInterface $urlGen)
    {
        $this->cache = $cache;
        $this->urlGenerator = $urlGen;
    }

    /** Gets the IP on the LAN */
    public function getLocalIp(): string
    {
        return $this->cache->get('my_ip_on_local_area_network', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString('5 minute'));
                    $name = '127.0.0.1';

                    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
                    if ($sock === false) {
                        throw new RuntimeException("socket_create() failed. Reason: " . socket_strerror(socket_last_error()));
                    }

                    $ret = @socket_connect($sock, "8.8.8.8", 53);
                    if ($ret === false) {
                        throw new RuntimeException("socket_connect() failed. Reason: " . socket_strerror(socket_last_error()));
                    }

                    socket_getsockname($sock, $name); // $name passed by reference
                    socket_close($sock);

                    return $name;
                });
    }

    /**
     * @see UrlGeneratorInterface::generate
     */
    public function generateUrlForExternalAccess(string $name, array $parameters = []): string
    {
        $url = $this->urlGenerator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
        $part = parse_url($url);
        $part['host'] = $this->getLocalIp();

        return $this->unparse_url($part);
    }

    // copy-pasted from https://www.php.net/manual/en/function.parse-url.php
    private function unparse_url(array $parsed_url): string
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

}
