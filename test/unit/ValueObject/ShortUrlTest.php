<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener\Test\Unit\ValueObject;

use Horde\Service\UrlShortener\ValueObject\ShortUrl;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ShortUrl value object.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
class ShortUrlTest extends TestCase
{
    public function testCreate(): void
    {
        $url = ShortUrl::create('https://tinyurl.com/abc123', 'TinyUrl');
        $this->assertEquals('https://tinyurl.com/abc123', $url->getUrl());
        $this->assertEquals('TinyUrl', $url->getService());
    }

    public function testCreateEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Short URL cannot be empty');
        ShortUrl::create('', 'TinyUrl');
    }

    public function testGetCode(): void
    {
        $url = ShortUrl::create('https://tinyurl.com/abc123', 'TinyUrl');
        $this->assertEquals('abc123', $url->getCode());
    }

    public function testGetDomain(): void
    {
        $url = ShortUrl::create('https://tinyurl.com/abc123', 'TinyUrl');
        $this->assertEquals('tinyurl.com', $url->getDomain());
    }

    public function testGetScheme(): void
    {
        $url = ShortUrl::create('https://tinyurl.com/abc123', 'TinyUrl');
        $this->assertEquals('https', $url->getScheme());
    }

    public function testStringable(): void
    {
        $url = ShortUrl::create('https://tinyurl.com/abc123', 'TinyUrl');
        $this->assertEquals('https://tinyurl.com/abc123', (string) $url);
    }
}
