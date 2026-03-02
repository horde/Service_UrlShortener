# Horde Service_UrlShortener

Modern PHP library for URL shortening services with native PSR-7/PSR-17/PSR-18 support.

## Installation

```bash
composer require horde/service-urlshortener
```

## Requirements

- PHP 8.1+
- PSR-18 HTTP client implementation
- PSR-17 HTTP factory implementation

## Quick Start

```php
use Horde\Service\UrlShortener\TinyUrl;
use Horde\Service\UrlShortener\ValueObject\LongUrl;

// Simple facade API
$shortener = new TinyUrl($httpClient, $requestFactory);
$shortUrl = $shortener->shorten('https://www.example.com/very/long/url');
echo $shortUrl; // https://tinyurl.com/abc123

// Rich domain API with metadata
$longUrl = LongUrl::fromString('https://www.example.com/very/long/url');
$result = $shortener->shortenWithMetadata($longUrl);

echo $result->getShortUrl()->toString();
echo "Saved: {$result->getPercentageSaved()}%";
echo "Reduction: {$result->getLengthReduction()} characters";
```

## Configuration

```php
use Horde\Service\UrlShortener\ValueObject\ShortenerConfig;

$config = ShortenerConfig::default()
    ->withTimeout(30)
    ->withCustomAlias('myalias')
    ->withExpiration(14); // days

$shortener = new TinyUrl($httpClient, $requestFactory, $config);
```

## Architecture

This library provides two API levels for progressive disclosure:

### Facade API
Simple string-based interface for basic usage:
```php
shorten(string|UriInterface $url): string
```

### Domain API
Rich value objects with detailed metadata:
```php
shortenWithMetadata(LongUrl $url): ShorteningResult
```

## Value Objects

- **LongUrl** - Input URL with validation and parsing
- **ShortUrl** - Output short URL with service metadata
- **ShorteningResult** - Complete result with metrics (length reduction, expiration, etc.)
- **ShortenerConfig** - Immutable configuration with fluent interface

## Supported Services

- TinyURL (built-in)
- Extensible via `UrlShortenerInterface`

## Exception Handling

```php
use Horde\Service\UrlShortener\UrlShortenerException;

try {
    $shortUrl = $shortener->shorten($longUrl);
} catch (UrlShortenerException $e) {
    // Handle shortening failures
}
```

## Legacy Support

The PSR-0 `lib/` directory provides backward compatibility wrappers for Horde 5 applications. New code should use the modern `Horde\Service\UrlShortener` namespace.

## Documentation

- [UPGRADING.md](doc/UPGRADING.md) - Migration guide from Horde 5
- [API Documentation](https://dev.horde.org/)

## License

LGPL 2.1 - See LICENSE file for details.

## Contributing

Contributions are welcome! Please follow:
- PHP 8.2+ with strict types
- PER-1 coding style
- Comprehensive unit tests
- Conventional Commits for commit messages
