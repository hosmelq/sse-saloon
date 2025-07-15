<?php

declare(strict_types=1);

namespace HosmelQ\SSE\Saloon\Traits;

use HosmelQ\SSE\Saloon\Responses\SSEResponse;
use Saloon\Http\Response;

trait HasServerSentEvents
{
    /**
     * Resolve the response class to use for Server-Sent Events.
     *
     * @return null|class-string<Response>
     */
    public function resolveResponseClass(): ?string
    {
        return SSEResponse::class;
    }

    /**
     * Get the default configuration for Server-Sent Events requests.
     *
     * @return array<string, mixed>
     */
    protected function defaultConfig(): array
    {
        return [
            'stream' => true,
            'timeout' => 0,
        ];
    }

    /**
     * Get the default headers for Server-Sent Events requests.
     *
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'text/event-stream',
            'Cache-Control' => 'no-store',
        ];
    }
}
