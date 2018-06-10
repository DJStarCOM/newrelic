<?php

namespace NewRelic\Test;

use NewRelic;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise;
use GuzzleHttp\Middleware;
use DJStarCOM\NewRelic\Insights;
use DJStarCOM\NewRelic\Entity\Insights\EventCollection;
use DJStarCOM\NewRelic\Entity\Insights\Event;
use PHPUnit\Framework\TestCase;

class InsightsTest extends TestCase
{
    /**
     * @var Insights
     */
    private $newRelicInsights;
    private $requestContainer = [];
    private $handler;

    public function setUp()
    {
        $this->requestContainer = [];
        $history = Middleware::history($this->requestContainer);
        $mock = new MockHandler([new Response(200, [])]);
        $this->handler = HandlerStack::create($mock);
        $this->handler->push($history);

        $client = new Client([
            'handler' => $this->handler,
            'base_uri' => 'http://SomeEndpoint/'
        ]);
        $this->newRelicInsights = new Insights($client, 'Mum-Ha');
    }

    public function testCanSendAsyncRequest()
    {
        $promise = $this->newRelicInsights->sendEvent(new EventCollection());

        $response = $promise->wait();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testFulfillMessageInterface()
    {
        $event = new Event();
        $event->eventType = "Purchase";
        $event->account = 3;
        $event->amount = 259.54;
        $events = new EventCollection();
        $events->add($event);

        $promise = $this->newRelicInsights->sendEvent($events);

        $promise->wait();
        $request = $this->requestContainer[0]['request'];
        $this->assertEquals(
            '[{"eventType":"Purchase","account":3,"amount":259.54}]',
            $request->getBody()->getContents()
        );
    }

    public function provideInvalidEventTypes()
    {
        return [
            'no type defined' => [null],
            'type as integer' => [123],
            'empty type' => [''],
        ];
    }

    /**
     * @dataProvider provideInvalidEventTypes
     * @expectedException \Exception
     */
    public function testEventType($type)
    {
        $event = new Event();
        $event->eventType = $type;
        $events = new EventCollection();
        $events->add($event);

        $this->newRelicInsights->sendEvent($events);
    }

    public function testFullInsightsUrlAreCalledFollowingRFC3986()
    {
        $client = new Client([
            'handler' => $this->handler,
            'base_uri' => 'http://SomeEndpoint/base/path/'
        ]);
        $this->newRelicInsights = new Insights($client, 'Mum-Ha');

        $promise = $this->newRelicInsights->sendEvent(new EventCollection());

        $promise->wait();
        $lastRequestPath = $this->requestContainer[0]['request']->getUri()->getPath();
        $this->assertEquals('/base/path/events', $lastRequestPath);
    }
}
