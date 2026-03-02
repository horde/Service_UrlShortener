<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener\Test\Unit\ValueObject;

use Horde\Service\UrlShortener\ValueObject\LongUrl;
use PHPUnit\Framework\TestCase;

/**
 * Extended tests for LongUrl value object.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
class LongUrlExtendedTest extends TestCase
{
    public function testFromPsr7Uri(): void
    {
        $uri = $this->createMock(\Psr\Http\Message\UriInterface::class);
        $uri->method('__toString')->willReturn('https://www.example.com/path');

        $longUrl = LongUrl::fromPsr7Uri($uri);

        $this->assertEquals('https://www.example.com/path', $longUrl->toString());
    }

    public function testVariousUrlFormats(): void
    {
        $validUrls = [
            'http://example.com',
            'https://example.com',
            'http://www.example.com',
            'https://www.example.com/path',
            'http://example.com:8080',
            'https://example.com/path?query=value',
            'http://example.com/path#anchor',
            'https://user:pass@example.com',
            'http://subdomain.example.com',
            'https://example.co.uk',
        ];

        foreach ($validUrls as $urlString) {
            $url = LongUrl::fromString($urlString);
            $this->assertEquals($urlString, $url->toString(), "Failed for: $urlString");
        }
    }

    public function testInvalidUrlFormats(): void
    {
        $invalidUrls = [
            '',
            'not-a-url',
            '//example.com',      // protocol-relative
            'javascript:alert(1)',
            'www.example.com',    // missing protocol
            'example',
            '192.168.1.1',        // IP without protocol
        ];

        foreach ($invalidUrls as $urlString) {
            try {
                LongUrl::fromString($urlString);
                $this->fail("Expected exception for: $urlString");
            } catch (\InvalidArgumentException $e) {
                $this->assertTrue(true); // Expected
            }
        }
    }

    public function testUrlWithComplexPath(): void
    {
        $url = LongUrl::fromString('https://example.com/path/to/very/long/resource');

        $this->assertEquals('/path/to/very/long/resource', $url->getPath());
        $this->assertEquals('example.com', $url->getDomain());
    }

    public function testUrlWithComplexQuery(): void
    {
        $url = LongUrl::fromString('https://example.com?foo=bar&baz=qux&arr[]=1&arr[]=2');

        $this->assertStringContainsString('foo=bar', $url->getQuery());
        $this->assertStringContainsString('baz=qux', $url->getQuery());
    }

    public function testUrlWithFragment(): void
    {
        $url = LongUrl::fromString('https://example.com/path#section');

        $this->assertEquals('/path', $url->getPath());
        // Note: parse_url doesn't include fragment in query
    }

    public function testGetPathWithNoPath(): void
    {
        $url = LongUrl::fromString('https://example.com');

        $this->assertEquals('', $url->getPath());
    }

    public function testGetQueryWithNoQuery(): void
    {
        $url = LongUrl::fromString('https://example.com/path');

        $this->assertEquals('', $url->getQuery());
    }

    public function testHttpVsHttps(): void
    {
        $http = LongUrl::fromString('http://example.com');
        $https = LongUrl::fromString('https://example.com');

        $this->assertFalse($http->isSecure());
        $this->assertTrue($https->isSecure());
        $this->assertEquals('http', $http->getScheme());
        $this->assertEquals('https', $https->getScheme());
    }

    public function testDifferentPorts(): void
    {
        $url80 = LongUrl::fromString('http://example.com:80');
        $url8080 = LongUrl::fromString('http://example.com:8080');
        $url443 = LongUrl::fromString('https://example.com:443');

        $this->assertStringContainsString(':80', $url80->toString());
        $this->assertStringContainsString(':8080', $url8080->toString());
        $this->assertStringContainsString(':443', $url443->toString());
    }
}
