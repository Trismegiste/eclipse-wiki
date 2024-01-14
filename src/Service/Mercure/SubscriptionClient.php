<?php

/*
 * eclipse-wiki
 */

namespace App\Service\Mercure;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Mercure Subscription API
 */
class SubscriptionClient
{

    public function __construct(protected HttpClientInterface $client, protected string $mercureLocalIp)
    {
        
    }

    public function getSubscriptions(): \stdClass
    {
        $response = $this->client->request('GET', "http://{$this->mercureLocalIp}/.well-known/mercure/subscriptions");

        return json_decode($response->getContent());
    }

}
