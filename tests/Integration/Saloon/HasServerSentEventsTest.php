<?php

declare(strict_types=1);

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as PsrResponse;
use HosmelQ\SSE\Saloon\Responses\SSEResponse;
use HosmelQ\SSE\Saloon\Tests\TestSupport\TestConnector;
use HosmelQ\SSE\Saloon\Tests\TestSupport\TestRequest;
use HosmelQ\SSE\Saloon\Traits\HasServerSentEvents;
use Saloon\Config;
use Saloon\Enums\Method;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Traits\Plugins\HasTimeout;

it('sends the SSE headers and streaming defaults', function (): void {
    $mockClient = new MockClient([MockResponse::make()]);
    $connector = new TestConnector()->withMockClient($mockClient);
    $response = $connector->send(new TestRequest());
    $pendingRequest = $mockClient->getLastPendingRequest();

    expect($response)->toBeInstanceOf(SSEResponse::class)
        ->and($pendingRequest->headers()->all())->toBe([
            'Accept' => 'text/event-stream',
            'Cache-Control' => 'no-store',
        ])
        ->and($pendingRequest->config()->get('stream'))->toBeTrue()
        ->and($pendingRequest->config()->get('read_timeout'))->toBe(60)
        ->and($pendingRequest->config()->get('timeout'))->toBeNull();
});

it('keeps request defaults while enforcing SSE headers and streaming', function (): void {
    $request = new class () extends Request {
        use HasServerSentEvents;

        protected Method $method = Method::GET;

        public function resolveEndpoint(): string
        {
            return '/sse';
        }

        protected function defaultConfig(): array
        {
            return ['read_timeout' => 300, 'stream' => false];
        }

        protected function defaultHeaders(): array
        {
            return [
                'Authorization' => 'Bearer token',
                'aCcEpT' => 'application/json',
                'cache-control' => 'max-age=60',
            ];
        }
    };
    $mockClient = new MockClient([MockResponse::make()]);
    $connector = new TestConnector()->withMockClient($mockClient);

    $connector->send($request);

    $pendingRequest = $mockClient->getLastPendingRequest();

    expect($pendingRequest->headers()->all())->toBe([
        'Authorization' => 'Bearer token',
        'Accept' => 'text/event-stream',
        'Cache-Control' => 'no-store',
    ])
        ->and($pendingRequest->config()->get('stream'))->toBeTrue()
        ->and($pendingRequest->config()->get('read_timeout'))->toBe(300);
});

it('replaces connector headers regardless of their case', function (array $headers): void {
    $connector = new class ($headers) extends Connector {
        public function __construct(private readonly array $connectorHeaders) {}

        public function resolveBaseUrl(): string
        {
            return 'https://example.com';
        }

        protected function defaultConfig(): array
        {
            return ['read_timeout' => 120];
        }

        protected function defaultHeaders(): array
        {
            return $this->connectorHeaders;
        }
    };
    $mockClient = new MockClient([MockResponse::make()]);

    $connector->withMockClient($mockClient)->send(new TestRequest());

    $pendingRequest = $mockClient->getLastPendingRequest();

    expect($pendingRequest->headers()->all())->toBe([
        'Accept' => 'text/event-stream',
        'Cache-Control' => 'no-store',
    ])
        ->and($pendingRequest->config()->get('read_timeout'))->toBe(120);
})->with([
    'canonical' => [['Accept' => 'application/json', 'Cache-Control' => 'max-age=60']],
    'lowercase' => [['accept' => 'application/json', 'cache-control' => 'max-age=60']],
    'duplicates' => [[
        'Accept' => 'application/json',
        'accept' => 'text/plain',
        'Cache-Control' => 'max-age=60',
        'CACHE-CONTROL' => 'no-cache',
    ]],
]);

it('supports the trait on the connector and request together', function (): void {
    $connector = new class () extends Connector {
        use HasServerSentEvents;

        public function resolveBaseUrl(): string
        {
            return 'https://example.com';
        }
    };
    $mockClient = new MockClient([MockResponse::make()]);
    $response = $connector->withMockClient($mockClient)->send(new TestRequest());
    $pendingRequest = $mockClient->getLastPendingRequest();

    expect($response)->toBeInstanceOf(SSEResponse::class)
        ->and($pendingRequest->headers()->all())->toBe([
            'Accept' => 'text/event-stream',
            'Cache-Control' => 'no-store',
        ])
        ->and($pendingRequest->config()->get('stream'))->toBeTrue()
        ->and($pendingRequest->config()->get('read_timeout'))->toBe(60);
});

it('keeps the connector timeout plugin settings', function (): void {
    $connector = new class () extends Connector {
        use HasTimeout;

        protected int $requestTimeout = 3;

        public function resolveBaseUrl(): string
        {
            return 'https://example.com';
        }
    };
    $mockClient = new MockClient([MockResponse::make()]);

    $connector->withMockClient($mockClient)->send(new TestRequest());

    $pendingRequest = $mockClient->getLastPendingRequest();

    expect($pendingRequest->config()->get('timeout'))->toBe(3.0)
        ->and($pendingRequest->config()->get('read_timeout'))->toBe(60)
        ->and($pendingRequest->config()->get('stream'))->toBeTrue();
});

it('passes SSE options and headers through the Guzzle sender', function (int $readTimeout): void {
    $connector = new TestConnector();
    $sender = $connector->sender();

    expect($sender)->toBeInstanceOf(GuzzleSender::class);

    $history = [];

    $sender->getHandlerStack()->setHandler(new MockHandler([
        new PsrResponse(200, ['Content-Type' => 'text/event-stream'], "data: hello\n\n"),
    ]));
    $sender->getHandlerStack()->push(Middleware::history($history));

    $request = new TestRequest();

    if ($readTimeout !== 60) {
        $request->config()->add('read_timeout', $readTimeout);
    }

    $response = $connector->send($request);

    expect($history)->toHaveCount(1)
        ->and($history[0]['options']['stream'])->toBeTrue()
        ->and($history[0]['options']['read_timeout'])->toBe($readTimeout)
        ->and($history[0]['options']['timeout'])->toBe(Config::$defaultRequestTimeout)
        ->and($history[0]['request']->getHeader('Accept'))->toBe(['text/event-stream'])
        ->and($history[0]['request']->getHeader('Cache-Control'))->toBe(['no-store'])
        ->and($response)->toBeInstanceOf(SSEResponse::class)
        ->and(iterator_to_array($response->asEventSource()->events())[0]->data)->toBe('hello');
})->with([60, 300]);
