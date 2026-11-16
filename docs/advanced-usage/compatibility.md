---
title: "Compatibility"
description: "The PHP setting streaming needs, and the Saloon features that read the whole response before your loop runs."
weight: 3
---

Streaming works when Guzzle streams the response and nothing reads its body before your loop.

## PHP's allow_url_fopen setting

Guzzle streams responses through PHP's HTTP stream wrapper, which needs the `allow_url_fopen` setting on. With it off, or with a connector whose Guzzle handler stack only uses cURL, Guzzle downloads the whole response before Saloon returns it, so every event arrives at once when the stream ends.

## Features that read the whole response

These Saloon features read the whole body when the response arrives. Turn them off for streaming requests:

- `debugResponse()` buffers the response, so your loop only gets the events after the stream ends.
- Recording a fixture with `MockResponse::fixture()` consumes the stream, so the loop gets no events while it records. Tests that replay the recorded fixture work.
- Caching responses with Saloon's cache plugin also reads the whole response.
