# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

- Require PHP 8.4, Saloon 4, and hosmelq/sse 1.0.
- Apply the SSE headers and streaming when Saloon sends the request, so requests no longer alias the trait's methods.
- Set a 60-second `read_timeout` instead of turning off Saloon's request timeout.
- Move the documentation to `docs/`.

See [UPGRADE.md](UPGRADE.md) for upgrade instructions.

## v0.1.0 - 2025-07-15

- Initial release.
