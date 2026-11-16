---
title: "Handling Errors"
description: "Check failed responses before reading events, and catch the exceptions Saloon and PHP SSE throw."
weight: 3
---

A streaming request can fail at three points:

- **Sending the request.** When the connection fails before a response arrives, Saloon throws `Saloon\Exceptions\Request\FatalRequestException`.
- **Opening the stream.** `asEventSource()` throws `HosmelQ\SSE\SSEProtocolException` when the response is not an event stream: a status other than `200`, or a `Content-Type` other than `text/event-stream`.
- **Reading the stream.** The `events()` loop throws `HosmelQ\SSE\SSEConnectionException` when reading fails, such as after an idle [timeout](../advanced-usage/timeouts).

## Failed responses

Saloon does not throw for error responses unless the connector uses its `AlwaysThrowOnErrors` plugin. Call `throw()` before reading events, so an authentication error raises Saloon's request exception instead of `SSEProtocolException`:

```php
$response = $connector->send(new StreamNotifications());

$response->throw();

foreach ($response->asEventSource()->events() as $event) {
    // ...
}
```

For the cases `throw()` lets through, such as a `200` response with the wrong content type, `SSEProtocolException::getResponse()` returns the PSR-7 response with its body unread.

## Streams that fail mid-way

```php
use HosmelQ\SSE\SSEConnectionException;

try {
    foreach ($response->asEventSource()->events() as $event) {
        // ...
    }
} catch (SSEConnectionException $e) {
    $e->getPrevious(); // The exception that caused it
}
```

A connection that drops does not always fail the read: the loop can also end as if the server had closed the stream. The [PHP SSE documentation](https://hosmelq.com/docs/sse-php) explains how to reconnect with the last event ID.
