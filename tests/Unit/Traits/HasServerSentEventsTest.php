<?php

declare(strict_types=1);

use HosmelQ\SSE\Saloon\Responses\SSEResponse;
use HosmelQ\SSE\Saloon\Tests\TestSupport\TestRequest;

it('returns default headers for Server-Sent Events', function (): void {
    $headers = (new TestRequest())->getDefaultHeaders();

    expect($headers)->toBe([
        'Accept' => 'text/event-stream',
        'Cache-Control' => 'no-store',
    ]);
});

it('returns default config for Server-Sent Events', function (): void {
    $config = (new TestRequest())->getDefaultConfig();

    expect($config)->toBe([
        'stream' => true,
        'timeout' => 0,
    ]);
});

it('sets the correct response class', function (): void {
    $class = (new TestRequest())->resolveResponseClass();

    expect($class)->toBe(SSEResponse::class);
});
