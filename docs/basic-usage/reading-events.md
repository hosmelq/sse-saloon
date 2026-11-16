---
title: "Reading Events"
description: "Turn the response into an event source, iterate its events, and use your own response class."
weight: 2
---

A request with the trait returns an `SSEResponse`. Its `asEventSource()` method returns a PHP SSE `EventSource`, and its `events()` method yields each event as the server sends it:

```php
$response = $connector->send(new StreamNotifications());

foreach ($response->asEventSource()->events() as $event) {
    $event->event; // The event type, "message" unless the event sets one
    $event->data;  // The data, with multiple "data" lines joined by "\n"
    $event->id;    // The last event ID, or ""
    $event->retry; // The reconnection time the event set, in milliseconds, or null
}
```

`asEventSource()` returns the same event source every time, so you can read the last event ID and reconnection time the stream set after the loop:

```php
$response->asEventSource()->getLastEventId();
$response->asEventSource()->getRetry();
```

When the request sends a `Last-Event-ID` header, from the connector or the request, the event source starts with that ID.

The [PHP SSE documentation](https://hosmelq.com/docs/sse-php) covers events, JSON data, stopping early, and reconnecting in detail.

## Your own response class

If a streaming request needs its own response class, extend `SSEResponse` and return it from the request's `resolveResponseClass()`:

```php
use HosmelQ\SSE\Saloon\Responses\SSEResponse;

final class ChatResponse extends SSEResponse
{
    // ...
}
```

To read events from any other Saloon response, pass its PSR-7 response to an `EventSource`:

```php
use HosmelQ\SSE\EventSource;

$source = new EventSource($response->getPsrResponse());
```
