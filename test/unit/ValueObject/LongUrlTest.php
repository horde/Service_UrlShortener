<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener\Test\Unit\ValueObject;

use Horde\Service\UrlShortener\ValueObject\LongUrl;
use PHPUnit\Framework\TestCase;

/**
 * Tests for LongUrl value object.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
class LongUrlTest extends TestCase
{
    public function testFromStringValid(): void
    {
        $url = LongUrl::fromString('https://www.example.com');
        $this->assertEquals('https://www.example.com', $url->toString());
    }

    public function testFromStringInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL');
        LongUrl::fromString('not-a-url');
    }

    public function testFromStringEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('URL cannot be empty');
        LongUrl::fromString('');
    }

    public function testGetDomain(): void
    {
        $url = LongUrl::fromString('https://www.example.com/path');
        $this->assertEquals('www.example.com', $url->getDomain());
    }

    public function testGetScheme(): void
    {
        $url = LongUrl::fromString('https://www.example.com');
        $this->assertEquals('https', $url->getScheme());
    }

    public function testIsSecure(): void
    {
        $https = LongUrl::fromString('https://www.example.com');
        $this->assertTrue($https->isSecure());

        $http = LongUrl::fromString('http://www.example.com');
        $this->assertFalse($http->isSecure());
    }

    public function testGetPath(): void
    {
        $url = LongUrl::fromString('https://www.example.com/path/to/page');
        $this->assertEquals('/path/to/page', $url->getPath());
    }

    public function testGetQuery(): void
    {
        $url = LongUrl::fromString('https://www.example.com?foo=bar&baz=qux');
        $this->assertEquals('foo=bar&baz=qux', $url->getQuery());
    }

    public function testStringable(): void
    {
        $url = LongUrl::fromString('https://www.example.com');
        $this->assertEquals('https://www.example.com', (string) $url);
    }
}
