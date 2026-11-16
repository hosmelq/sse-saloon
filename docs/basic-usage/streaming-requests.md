---
title: "Streaming Requests"
description: "Add the HasServerSentEvents trait to a request, send POST bodies, and keep your own headers and config."
weight: 1
---

The `HasServerSentEvents` trait turns a Saloon request into a streaming request. When Saloon sends it, the trait:

- Sets the `Accept` header to `text/event-stream` and the `Cache-Control` header to `no-store`.
- Turns on Guzzle's `stream` option, so you receive each event when it arrives instead of after the response ends.
- Sets Guzzle's `read_timeout` to 60 seconds, unless your connector or request sets it. [Timeouts](../advanced-usage/timeouts) explains why.
- Makes the connector return an `SSEResponse`, which [reads the events](reading-events).

The trait applies the headers and the `stream` option after Saloon merges the connector's and the request's properties, so they win over an `Accept` header the connector sends with every request, such as `application/json`.

## POST requests

Many APIs stream their response to a POST request. Combine the trait with a Saloon body:

```php
use HosmelQ\SSE\Saloon\Traits\HasServerSentEvents;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class CreateResponse extends Request implements HasBody
{
    use HasJsonBody;
    use HasServerSentEvents;

    protected Method $method = Method::POST;

    public function __construct(private string $input) {}

    public function resolveEndpoint(): string
    {
        return '/responses';
    }

    protected function defaultBody(): array
    {
        return ['input' => $this->input, 'stream' => true];
    }
}
```

## Your own headers and config

Define `defaultHeaders()` and `defaultConfig()` as in any other Saloon request. The trait does not define them, so there is nothing to alias:

```php
protected function defaultHeaders(): array
{
    return ['Authorization' => 'Bearer '.$this->token];
}

protected function defaultConfig(): array
{
    return ['read_timeout' => 300];
}
```
