<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener\Test\Unit;

use Horde\Service\UrlShortener\TinyUrl;
use Horde\Service\UrlShortener\UrlShortenerException;
use Horde\Service\UrlShortener\ValueObject\LongUrl;
use Horde\Service\UrlShortener\ValueObject\ShortenerConfig;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Tests for TinyUrl shortener.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
class TinyUrlTest extends TestCase
{
    public function testShortenFacadeApi(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturn('https://tinyurl.com/abc123');
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $shortener = new TinyUrl($httpClient, $requestFactory);
        $result = $shortener->shorten('https://www.example.com/long/url');

        $this->assertEquals('https://tinyurl.com/abc123', $result);
    }

    public function testShortenWithMetadataDomainApi(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturn('https://tinyurl.com/abc123');
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $shortener = new TinyUrl($httpClient, $requestFactory);
        $longUrl = LongUrl::fromString('https://www.example.com/long/url');
        $result = $shortener->shortenWithMetadata($longUrl);

        $this->assertEquals('https://tinyurl.com/abc123', $result->getShortUrl()->toString());
        $this->assertEquals('www.example.com', $result->getOriginalUrl()->getDomain());
        $this->assertEquals('TinyUrl', $result->getShortUrl()->getService());
        $this->assertGreaterThan(0, $result->getLengthReduction());
    }

    public function testShortenThrowsOnHttpError(): void
    {
        $this->expectException(UrlShortenerException::class);
        $this->expectExceptionMessage('Failed to shorten URL via TinyUrl');

        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);

        // Throw a PSR-18 client exception
        $exception = new class('Network error') extends \RuntimeException implements \Psr\Http\Client\ClientExceptionInterface {};
        $httpClient->method('sendRequest')->willThrowException($exception);

        $shortener = new TinyUrl($httpClient, $requestFactory);
        $shortener->shorten('https://www.example.com');
    }

    public function testShortenThrowsOnNon200Status(): void
    {
        $this->expectException(UrlShortenerException::class);
        $this->expectExceptionMessage('TinyUrl API returned status 500');

        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(500);
        $response->method('getReasonPhrase')->willReturn('Internal Server Error');
        $httpClient->method('sendRequest')->willReturn($response);

        $shortener = new TinyUrl($httpClient, $requestFactory);
        $shortener->shorten('https://www.example.com');
    }

    public function testGetServiceName(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);

        $shortener = new TinyUrl($httpClient, $requestFactory);
        $this->assertEquals('TinyUrl', $shortener->getServiceName());
    }

    public function testWithConfig(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $config = ShortenerConfig::default()->withTimeout(30)->withExpiration(7);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturn('https://tinyurl.com/abc123');
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $shortener = new TinyUrl($httpClient, $requestFactory, $config);
        $longUrl = LongUrl::fromString('https://www.example.com');
        $result = $shortener->shortenWithMetadata($longUrl);

        $this->assertTrue($result->hasExpiration());
    }
}
