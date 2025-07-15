<?php

declare(strict_types=1);

namespace HosmelQ\SSE\Saloon\Responses;

use HosmelQ\SSE\EventSource;
use HosmelQ\SSE\SSEProtocolException;
use Saloon\Http\Response;

class SSEResponse extends Response
{
    /**
     * Convert the response to an EventSource for event streaming.
     *
     * @throws SSEProtocolException
     */
    public function asEventSource(): EventSource
    {
        return new EventSource($this->getPsrResponse());
    }
}
