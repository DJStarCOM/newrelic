<?php

namespace DJStarCOM\NewRelic;

use GuzzleHttp\Client;
use DJStarCOM\NewRelic\Entity\Insights\EventCollection;
use Respect\Validation\Validator;

class Insights
{
    private $key;
    private $client;

    public function __construct(Client $client, $key)
    {
        $this->client = $client;
        $this->key = $key;
        $baseUrl = $this->client->getConfig('base_uri');
        Validator::notEmpty()
            ->url()
            ->endsWith('/')
            ->setName("URL for NewRelic's Insights API must be valid and have a trailing slash")
            ->assert($baseUrl);
    }

    public function sendEvent(EventCollection $events)
    {
        $promise = $this->client->postAsync('events', [
            'body' => json_encode($events),
            'headers' => [
                'X-Insert-Key' => $this->key,
                'Content-Type' => 'application/json',
            ]
        ]);

        return $promise;
    }
}
