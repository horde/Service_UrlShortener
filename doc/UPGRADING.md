# Upgrading to Service_UrlShortener 2.0

## Overview

Version 2.0 introduces a modern PSR-4 implementation with native PSR-7/PSR-17/PSR-18 support. The legacy PSR-0 `lib/` wrappers remain for backward compatibility.

## Breaking Changes

### Namespace Change
```php
// Old (Horde 5)
use Horde_Service_UrlShortener_TinyUrl;

// New (PSR-4)
use Horde\Service\UrlShortener\TinyUrl;
```

### Exception Handling
```php
// Old - Extended Horde_Exception_Wrapped (supports PEAR_Error)
catch (Horde_Service_UrlShortener_Exception $e)

// New - Extends RuntimeException (pure PHP exceptions)
catch (Horde\Service\UrlShortener\UrlShortenerException $e)
```

### HTTP Client
```php
// Old - Horde_Http_Client
$client = new Horde_Http_Client();
$shortener = new Horde_Service_UrlShortener_TinyUrl($client);

// New - PSR-18 ClientInterface + PSR-17 RequestFactoryInterface
$shortener = new TinyUrl($httpClient, $requestFactory);
```

### Configuration
```php
// Old - Array configuration
$shortener = new Horde_Service_UrlShortener_TinyUrl($client, ['timeout' => 30]);

// New - Immutable value object
$config = ShortenerConfig::default()->withTimeout(30);
$shortener = new TinyUrl($httpClient, $requestFactory, $config);
```

## Migration Strategies

### Strategy 1: Keep Using Legacy Wrappers
Minimal changes required. The PSR-0 wrappers in `lib/` delegate to modern implementation:

```php
// Still works in Horde 6
use Horde_Service_UrlShortener_TinyUrl;

$client = new Horde_Http_Client();
$shortener = new Horde_Service_UrlShortener_TinyUrl($client);
$result = $shortener->shorten('https://example.com');
```

**Note:** Legacy wrappers will be deprecated in a future major version.

### Strategy 2: Gradual Migration
Use facade API for minimal code changes:

```php
use Horde\Service\UrlShortener\TinyUrl;

$shortener = new TinyUrl($httpClient, $requestFactory);
$shortUrl = $shortener->shorten('https://example.com'); // Still returns string
```

### Strategy 3: Full Modern Implementation
Leverage rich domain model:

```php
use Horde\Service\UrlShortener\TinyUrl;
use Horde\Service\UrlShortener\ValueObject\LongUrl;
use Horde\Service\UrlShortener\ValueObject\ShortenerConfig;

$config = ShortenerConfig::default()
    ->withTimeout(30)
    ->withExpiration(7);

$shortener = new TinyUrl($httpClient, $requestFactory, $config);

$longUrl = LongUrl::fromString('https://example.com/very/long/url');
$result = $shortener->shortenWithMetadata($longUrl);

// Rich metadata available
echo $result->getPercentageSaved();
echo $result->getLengthReduction();
echo $result->getCustomAlias();
```

## Removed Features

### Horde\Url\Url Support
The PSR-4 variant uses PSR-7 `UriInterface`, not `Horde\Url\Url`. Callers should convert to PSR-7 URI or use the facade API with strings:

```php
// Not supported in PSR-4
$hordeUrl = new Horde\Url\Url('...');
$shortener->shorten($hordeUrl); // Won't work

// Use string instead
$shortener->shorten($hordeUrl->toString());

// Or convert to PSR-7
$shortener->shorten($psr7Uri);
```

### PEAR_Error Support
The PSR-4 exceptions extend `RuntimeException`, not `Horde_Exception_Wrapped`. Previous exceptions are preserved properly:

```php
try {
    // ...
} catch (UrlShortenerException $e) {
    $previous = $e->getPrevious(); // Works correctly
}
```

## Benefits of Upgrading

1. **Type Safety** - Strict types, readonly properties, comprehensive type declarations
2. **Modern Standards** - PSR-7/17/18 HTTP, PSR-4 autoloading
3. **Better Testing** - Comprehensive test coverage (59 tests)
4. **Value Objects** - Immutable, validated domain objects
5. **Progressive Disclosure** - Simple facade + rich domain APIs
6. **No Legacy Baggage** - Pure modern PHP, no PEAR dependencies

## Timeline

- **2.0.0** (Current) - PSR-4 implementation, legacy wrappers maintained
- **3.0.0** (Future) - Legacy wrappers deprecated with E_USER_DEPRECATED notices
- **4.0.0** (Future) - Legacy wrappers removed

## Support

For migration assistance, see:
- [README.md](../README.md) - Usage examples
- [Unit Tests](../test/unit/) - Working examples
- [Horde Mailing List](https://lists.horde.org/)
