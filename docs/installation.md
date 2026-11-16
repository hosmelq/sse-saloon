---
title: "Installation"
description: "Install SSE Saloon Plugin with Composer. It requires PHP 8.4 or later and Saloon 4."
weight: 2
---

## Requirements

SSE Saloon Plugin requires PHP 8.4 or later and Saloon 4 with its Guzzle sender, the one Saloon uses by default.

## Install the package

Install the package with Composer:

```bash
composer require hosmelq/sse-saloon
```

Composer also installs [PHP SSE](https://hosmelq.com/docs/sse-php), which parses the events.

## Next steps

Add the trait to a [streaming request](basic-usage/streaming-requests).
