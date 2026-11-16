---
title: "Timeouts"
description: "Set how long a stream may stay silent, and see how Saloon's request timeout applies to streaming requests."
weight: 1
---

An event stream stays open for as long as the server keeps sending. Guzzle's `read_timeout` option sets how many seconds the stream may stay silent before reading fails with `SSEConnectionException`. The trait sets it to 60 seconds unless your connector or request sets it.

Raise it above the longest pause the server makes between events or keep-alive comments:

```php
protected function defaultConfig(): array
{
    return ['read_timeout' => 300];
}
```

## Saloon's request timeout

Saloon gives every request a 30-second timeout, which its `HasTimeout` plugin changes. The trait leaves it alone. It covers the time until the response headers arrive, and from then on `read_timeout` governs the stream, so a stream can stay open far longer than the request timeout.
