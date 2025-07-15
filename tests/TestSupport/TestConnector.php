<?php

declare(strict_types=1);

namespace HosmelQ\SSE\Saloon\Tests\TestSupport;

use Saloon\Http\Connector;

final class TestConnector extends Connector
{
    public function resolveBaseUrl(): string
    {
        return 'https://example.com';
    }
}
