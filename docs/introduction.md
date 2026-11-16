---
title: "Introduction"
description: "SSE Saloon Plugin turns Saloon requests into Server-Sent Events streams and yields each event as the server sends it."
weight: 1
---

SSE Saloon Plugin adds Server-Sent Events (SSE) streaming to Saloon requests. Add a trait to a request, send it with your connector as usual, and iterate the events as the server sends them. [PHP SSE](https://hosmelq.com/docs/sse-php) parses the events.

## Quick start

After [installing the package](installation), add the `HasServerSentEvents` trait to a request:

```php
use HosmelQ\SSE\Saloon\Traits\HasServerSentEvents;
use Saloon\Enums\Method;
use Saloon\Http\Request;

final class StreamNotifications extends Request
{
    use HasServerSentEvents;

    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/notifications/stream';
    }
}
```

Send it and iterate the events:

```php
$response = $connector->send(new StreamNotifications());

foreach ($response->asEventSource()->events() as $event) {
    echo $event->event.': '.$event->data.PHP_EOL;
}
```

The loop ends when the server closes the stream.

## Next steps

- [Streaming Requests](basic-usage/streaming-requests) explains what the trait changes and how to send POST bodies.
- [Reading Events](basic-usage/reading-events) covers the response and its events.
- [Handling Errors](basic-usage/handling-errors) explains which exceptions to catch.
