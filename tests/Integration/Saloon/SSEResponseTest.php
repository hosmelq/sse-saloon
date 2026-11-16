<?php

declare(strict_types=1);

use HosmelQ\SSE\EventSource;
use HosmelQ\SSE\Saloon\Tests\TestSupport\TestConnector;
use HosmelQ\SSE\Saloon\Tests\TestSupport\TestRequest;
use HosmelQ\SSE\ServerSentEvent;
use HosmelQ\SSE\SSEProtocolException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('rejects a successful response with a non-SSE content type', function (): void {
    $connector = new TestConnector()->withMockClient(new MockClient([
        MockResponse::make('{}', 200, ['Content-Type' => 'application/json']),
    ]));
    $response = $connector->send(new TestRequest());

    expect(fn () => $response->asEventSource())->toThrow(
        SSEProtocolException::class,
        "Expected Content-Type 'text/event-stream', got 'application/json'.",
    );
});

it('parses events and retains the same event source and last event ID', function (): void {
    $connector = new TestConnector()->withMockClient(new MockClient([
        MockResponse::make(
            "id: 1\nevent: update\ndata: first\n\nid: 2\ndata: second\n\n",
            200,
            ['Content-Type' => 'text/event-stream'],
        ),
    ]));
    $response = $connector->send(new TestRequest());
    $source = $response->asEventSource();

    expect($source)->toBeInstanceOf(EventSource::class)
        ->and($response->asEventSource())->toBe($source);

    $events = iterator_to_array($response->asEventSource()->events());

    expect($events)->toHaveCount(2)
        ->and($events[0])->toBeInstanceOf(ServerSentEvent::class)
        ->and($events[0]->data)->toBe('first')
        ->and($events[0]->event)->toBe('update')
        ->and($events[0]->id)->toBe('1')
        ->and($events[1]->data)->toBe('second')
        ->and($events[1]->event)->toBe('message')
        ->and($events[1]->id)->toBe('2')
        ->and($response->asEventSource())->toBe($source)
        ->and($response->asEventSource()->getLastEventId())->toBe('2')
        ->and($source->getResponse())->toBe($response->getPsrResponse());
});

it('starts the event source with the Last-Event-ID the request sent', function (): void {
    $connector = new TestConnector()->withMockClient(new MockClient([
        MockResponse::make(
            "retry: 1000\n\ndata: next\n\n",
            200,
            ['Content-Type' => 'text/event-stream'],
        ),
    ]));
    $request = new TestRequest();

    $request->headers()->add('Last-Event-ID', '7');

    $source = $connector->send($request)->asEventSource();
    $events = iterator_to_array($source->events());

    expect($events)->toHaveCount(1)
        ->and($events[0]->id)->toBe('7')
        ->and($source->getLastEventId())->toBe('7')
        ->and($source->getRetry())->toBe(1_000);
});

it('preserves the error response and lets Saloon throw before SSE validation', function (): void {
    $connector = new TestConnector()->withMockClient(new MockClient([
        MockResponse::make('{"error":"invalid token"}', 401, [
            'Content-Type' => 'application/json',
        ]),
    ]));
    $response = $connector->send(new TestRequest());

    try {
        $response->asEventSource();

        test()->fail('Expected SSE protocol validation to fail.');
    } catch (SSEProtocolException $sseProtocolException) {
        expect($sseProtocolException->getResponse())->toBe($response->getPsrResponse())
            ->and($sseProtocolException->getResponse()->getStatusCode())->toBe(401)
            ->and((string) $sseProtocolException->getResponse()->getBody())->toBe(
                '{"error":"invalid token"}',
            );
    }

    expect(fn () => $response->throw()->asEventSource())->toThrow(RequestException::class);
});
