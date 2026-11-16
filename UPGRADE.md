# Upgrade Guide

## Upgrading to 1.0 from 0.1

### Runtime requirements

The plugin now requires PHP 8.4, Saloon 4, and `hosmelq/sse` 1.0. Every Saloon release before
4.0 has security advisories, so Composer no longer installs them by default. Read the
[`hosmelq/sse` upgrade guide](https://github.com/hosmelq/sse-php/blob/v1.0.0/UPGRADE.md) for the
changes in event parsing and exceptions.

### Trait methods

`HasServerSentEvents` no longer defines `defaultHeaders()` and `defaultConfig()`. It applies its
headers and config when Saloon sends the request, so your own methods no longer replace them.
Remove the trait method aliases from your requests, or PHP fails because the aliased methods no
longer exist:

```php
// Before
use HasServerSentEvents {
    defaultConfig as defaultSSEConfig;
    defaultHeaders as defaultSSEHeaders;
}

protected function defaultHeaders(): array
{
    return array_merge($this->defaultSSEHeaders(), [
        'Authorization' => 'Bearer '.$this->token,
    ]);
}

// After
use HasServerSentEvents;

protected function defaultHeaders(): array
{
    return ['Authorization' => 'Bearer '.$this->token];
}
```

### Timeouts

The trait no longer sets Saloon's request timeout to `0`, so the connector's timeout, 30 seconds
by default, applies until the response headers arrive. It now sets Guzzle's `read_timeout` to 60
seconds, which limits how long the stream may stay silent. Set `read_timeout` in your connector or
request config to change it.

### Error responses

`asEventSource()` now throws `SSEProtocolException` for any status other than 200, not only for a
wrong `Content-Type`. Call `$response->throw()` before reading events to get Saloon's request
exceptions for error responses.
