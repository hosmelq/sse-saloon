<?php

declare(strict_types=1);

namespace HosmelQ\SSE\Saloon\Traits;

use HosmelQ\SSE\Saloon\Responses\SSEResponse;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

/**
 * @phpstan-ignore trait.unused
 */
trait HasServerSentEvents
{
    public function bootHasServerSentEvents(PendingRequest $pendingRequest): void
    {
        $pendingRequest->config()->add('read_timeout', 60);

        $pendingRequest->middleware()->onRequest(function (PendingRequest $pendingRequest): void {
            foreach (array_keys($pendingRequest->headers()->all()) as $name) {
                if (in_array(mb_strtolower($name), ['accept', 'cache-control'], true)) {
                    $pendingRequest->headers()->remove($name);
                }
            }

            $pendingRequest->headers()->merge([
                'Accept' => 'text/event-stream',
                'Cache-Control' => 'no-store',
            ]);
            $pendingRequest->config()->add('stream', true);
        });
    }

    /**
     * Resolve the response class to use for Server-Sent Events.
     *
     * @return null|class-string<Response>
     */
    public function resolveResponseClass(): null|string
    {
        return SSEResponse::class;
    }
}
