<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security();

arch('annotations')
    ->expect('HosmelQ\SSE\Saloon')
    ->toHaveMethodsDocumented()
    ->toHavePropertiesDocumented();

arch('strict types')
    ->expect('HosmelQ\SSE\Saloon')
    ->toUseStrictTypes();
