<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener\Test\Unit\ValueObject;

use Horde\Service\UrlShortener\ValueObject\LongUrl;
use Horde\Service\UrlShortener\ValueObject\ShortUrl;
use Horde\Service\UrlShortener\ValueObject\ShorteningResult;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

/**
 * Tests for ShorteningResult value object.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
class ShorteningResultTest extends TestCase
{
    public function testConstruction(): void
    {
        $longUrl = LongUrl::fromString('https://www.example.com/very/long/url');
        $shortUrl = ShortUrl::create('https://tinyurl.com/abc', 'TinyUrl');
        $createdAt = new DateTimeImmutable();

        $result = new ShorteningResult($longUrl, $shortUrl, $createdAt);

        $this->assertSame($longUrl, $result->getOriginalUrl());
        $this->assertSame($shortUrl, $result->getShortUrl());
        $this->assertSame($createdAt, $result->getCreatedAt());
        $this->assertNull($result->getCustomAlias());
        $this->assertNull($result->getExpiresAt());
    }

    public function testWithExpiration(): void
    {
        $longUrl = LongUrl::fromString('https://www.example.com');
        $shortUrl = ShortUrl::create('https://tinyurl.com/abc', 'TinyUrl');
        $createdAt = new DateTimeImmutable();
        $expiresAt = $createdAt->modify('+7 days');

        $result = new ShorteningResult($longUrl, $shortUrl, $createdAt, null, $expiresAt);

        $this->assertTrue($result->hasExpiration());
        $this->assertSame($expiresAt, $result->getExpiresAt());
        $this->assertFalse($result->isExpired());
    }

    public function testIsExpired(): void
    {
        $longUrl = LongUrl::fromString('https://www.example.com');
        $shortUrl = ShortUrl::create('https://tinyurl.com/abc', 'TinyUrl');
        $createdAt = new DateTimeImmutable();
        $expiresAt = $createdAt->modify('-1 day');

        $result = new ShorteningResult($longUrl, $shortUrl, $createdAt, null, $expiresAt);

        $this->assertTrue($result->hasExpiration());
        $this->assertTrue($result->isExpired());
    }

    public function testGetLengthReduction(): void
    {
        $longUrl = LongUrl::fromString('https://www.example.com/very/long/url');
        $shortUrl = ShortUrl::create('https://tinyurl.com/abc', 'TinyUrl');
        $createdAt = new DateTimeImmutable();

        $result = new ShorteningResult($longUrl, $shortUrl, $createdAt);

        $expected = strlen((string) $longUrl) - strlen((string) $shortUrl);
        $this->assertEquals($expected, $result->getLengthReduction());
        $this->assertGreaterThan(0, $result->getLengthReduction());
    }

    public function testGetPercentageSaved(): void
    {
        $longUrl = LongUrl::fromString('https://www.example.com/very/long/url');
        $shortUrl = ShortUrl::create('https://tinyurl.com/abc', 'TinyUrl');
        $createdAt = new DateTimeImmutable();

        $result = new ShorteningResult($longUrl, $shortUrl, $createdAt);

        $percentage = $result->getPercentageSaved();
        $this->assertGreaterThan(0, $percentage);
        $this->assertLessThan(100, $percentage);
    }

    public function testCustomAlias(): void
    {
        $longUrl = LongUrl::fromString('https://www.example.com');
        $shortUrl = ShortUrl::create('https://tinyurl.com/myalias', 'TinyUrl');
        $createdAt = new DateTimeImmutable();

        $result = new ShorteningResult($longUrl, $shortUrl, $createdAt, 'myalias');

        $this->assertEquals('myalias', $result->getCustomAlias());
    }
}
