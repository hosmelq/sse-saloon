<?php

declare(strict_types=1);

namespace HosmelQ\SSE\Saloon\Responses;

use HosmelQ\SSE\EventSource;
use HosmelQ\SSE\SSEProtocolException;
use Saloon\Http\Response;

class SSEResponse extends Response
{
    private null|EventSource $eventSource = null;

    /**
     * Convert the response to an EventSource for event streaming, starting with the ID the
     * request sent in its Last-Event-ID header.
     *
     * @throws SSEProtocolException
     */
    public function asEventSource(): EventSource
    {
        return $this->eventSource ??= new EventSource(
            $this->getPsrResponse(),
            $this->getPsrRequest()->getHeader('Last-Event-ID')[0] ?? '',
        );
    }
}
