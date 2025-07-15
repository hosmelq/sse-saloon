# SSE Saloon Plugin

A [Saloon](https://docs.saloon.dev/) plugin that adds Server-Sent Events support to your HTTP requests. Built on the [hosmelq/sse-php](https://github.com/hosmelq/sse-php) library for WHATWG-compliant SSE parsing.

## Requirements

- PHP 8.2+

## Installation

```bash
composer require hosmelq/sse-saloon
```

## Basic Usage

Add the `HasServerSentEvents` trait to your request class:

```php
use HosmelQ\SSE\Saloon\Traits\HasServerSentEvents;
use Saloon\Http\Request;
use Saloon\Enums\Method;

class StreamNotifications extends Request
{
    use HasServerSentEvents;

    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/notifications/stream';
    }
}
```

Send the request and process events:

```php
$response = $connector->send(new StreamNotifications());

foreach ($response->asEventSource()->events() as $event) {
    echo "Data: {$event->data}\n";
    echo "Event: {$event->event}\n";
    echo "ID: {$event->id}\n";
}
```

## Custom Headers and Configuration

Override the default methods to add authentication, custom headers, or configure connection settings:

```php
use HosmelQ\SSE\Saloon\Traits\HasServerSentEvents;
use Saloon\Http\Request;
use Saloon\Enums\Method;

class CustomizedStream extends Request
{
    use HasServerSentEvents {
        defaultConfig as defaultSSEConfig;
        defaultHeaders as defaultSSEHeaders;
    }

    protected Method $method = Method::GET;

    public function __construct(private int $readTimeout, private string $token) {}

    public function resolveEndpoint(): string
    {
        return '/api/stream';
    }

    protected function defaultConfig(): array
    {
        return array_merge($this->defaultSSEConfig(), [
            'read_timeout' => $this->readTimeout,
        ]);
    }

    protected function defaultHeaders(): array
    {
        return array_merge($this->defaultSSEHeaders(), [
            'Authorization' => 'Bearer ' . $this->token,
        ]);
    }
}
```

For detailed information about event processing, parsing, and handling, see the [hosmelq/sse-php](https://github.com/hosmelq/sse-php) documentation.

## Error Handling

```php
use HosmelQ\SSE\SSEProtocolException;

try {
    $response = $connector->send(new EventStreamRequest());
    
    foreach ($response->asEventSource()->events() as $event) {
    }
} catch (SSEProtocolException $e) {
    echo 'SSE Error: ' . $e->getMessage();
}
```

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.

## Credits

- [Hosmel Quintana](https://github.com/hosmelq)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
