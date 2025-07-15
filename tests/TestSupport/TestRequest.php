<?php

declare(strict_types=1);

namespace HosmelQ\SSE\Saloon\Tests\TestSupport;

use HosmelQ\SSE\Saloon\Traits\HasServerSentEvents;
use Saloon\Enums\Method;
use Saloon\Http\Request;

final class TestRequest extends Request
{
    use HasServerSentEvents;

    protected Method $method = Method::GET;

    public function getDefaultConfig(): array
    {
        return $this->defaultConfig();
    }

    public function getDefaultHeaders(): array
    {
        return $this->defaultHeaders();
    }

    public function resolveEndpoint(): string
    {
        return '/sse';
    }
}
