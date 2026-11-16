---
title: "Testing"
description: "Fake streaming responses with Saloon's MockClient and check the headers and config the trait set."
weight: 2
---

Saloon's `MockClient` works with streaming requests. Return a `MockResponse` whose body is the event stream and whose `Content-Type` is `text/event-stream`:

```php
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

$mockClient = new MockClient([
    StreamNotifications::class => MockResponse::make(
        "event: order.shipped\ndata: {\"order\": 1001}\n\n",
        200,
        ['Content-Type' => 'text/event-stream'],
    ),
]);

$response = $connector->withMockClient($mockClient)->send(new StreamNotifications());

$events = iterator_to_array($response->asEventSource()->events());
```

Fake responses skip Guzzle, so their events arrive all at once, and timeouts never apply. `getLastPendingRequest()` returns the request Saloon prepared, with the headers and config the trait set:

```php
$pendingRequest = $mockClient->getLastPendingRequest();

$pendingRequest->headers()->get('Accept'); // "text/event-stream"
$pendingRequest->config()->get('stream'); // true
```
