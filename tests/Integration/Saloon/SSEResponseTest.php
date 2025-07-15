<?php

declare(strict_types=1);

use HosmelQ\SSE\EventSource;
use HosmelQ\SSE\Saloon\Tests\TestSupport\TestConnector;
use HosmelQ\SSE\Saloon\Tests\TestSupport\TestRequest;
use HosmelQ\SSE\SSEProtocolException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('throws when Content-Type is not text/event-stream', function (): void {
    $connector = new TestConnector();

    $connector->withMockClient(new MockClient([
        TestRequest::class => MockResponse::make('', 200, ['Content-Type' => 'application/json']),
    ]));

    $response = $connector->send(new TestRequest());

    $response->asEventSource();
})->throws(SSEProtocolException::class, "Expected 'text/event-stream', got 'application/json'");

it('returns EventSource when Content-Type is text/event-stream', function (): void {
    $connector = new TestConnector();

    $connector->withMockClient(new MockClient([
        TestRequest::class => MockResponse::make('', 200, ['Content-Type' => 'text/event-stream']),
    ]));

    $response = $connector->send(new TestRequest());

    expect($response->asEventSource())->toBeInstanceOf(EventSource::class);
});
