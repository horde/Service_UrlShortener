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
use Psr\Http\Message\UriInterface;

/**
 * Extended tests for TinyUrl shortener.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
class TinyUrlExtendedTest extends TestCase
{
    public function testShortenWithPsr7Uri(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $uri = $this->createMock(UriInterface::class);

        $uri->method('__toString')->willReturn('https://www.example.com');
        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturn('https://tinyurl.com/test');
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $shortener = new TinyUrl($httpClient, $requestFactory);
        $result = $shortener->shorten($uri);

        $this->assertEquals('https://tinyurl.com/test', $result);
    }

    public function testShortenTrimsWhitespace(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturn("  https://tinyurl.com/abc  \n");
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $shortener = new TinyUrl($httpClient, $requestFactory);
        $result = $shortener->shorten('https://example.com');

        $this->assertEquals('https://tinyurl.com/abc', $result);
    }

    public function testShortenThrowsOnEmptyResponse(): void
    {
        $this->expectException(UrlShortenerException::class);
        $this->expectExceptionMessage('empty response');

        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturn('');
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $shortener = new TinyUrl($httpClient, $requestFactory);
        $shortener->shorten('https://example.com');
    }

    public function testShortenWithMetadataPreservesConfig(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $config = ShortenerConfig::default()
            ->withCustomAlias('myalias')
            ->withExpiration(14);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturn('https://tinyurl.com/myalias');
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $shortener = new TinyUrl($httpClient, $requestFactory, $config);
        $longUrl = LongUrl::fromString('https://example.com');
        $result = $shortener->shortenWithMetadata($longUrl);

        $this->assertEquals('myalias', $result->getCustomAlias());
        $this->assertNotNull($result->getExpiresAt());
        $this->assertTrue($result->hasExpiration());
    }

    public function testMultipleShorteningOperations(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $requestFactory->method('createRequest')->willReturn($request);
        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturnOnConsecutiveCalls(
            'https://tinyurl.com/abc',
            'https://tinyurl.com/def',
            'https://tinyurl.com/ghi'
        );
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $shortener = new TinyUrl($httpClient, $requestFactory);

        $result1 = $shortener->shorten('https://example.com/1');
        $result2 = $shortener->shorten('https://example.com/2');
        $result3 = $shortener->shorten('https://example.com/3');

        $this->assertEquals('https://tinyurl.com/abc', $result1);
        $this->assertEquals('https://tinyurl.com/def', $result2);
        $this->assertEquals('https://tinyurl.com/ghi', $result3);
    }

    public function testShortenWithVariousStatusCodes(): void
    {
        $statusCodes = [400, 401, 403, 404, 429, 500, 502, 503];

        foreach ($statusCodes as $statusCode) {
            $httpClient = $this->createMock(ClientInterface::class);
            $requestFactory = $this->createMock(RequestFactoryInterface::class);
            $request = $this->createMock(RequestInterface::class);
            $response = $this->createMock(ResponseInterface::class);

            $requestFactory->method('createRequest')->willReturn($request);
            $response->method('getStatusCode')->willReturn($statusCode);
            $response->method('getReasonPhrase')->willReturn('Error');
            $httpClient->method('sendRequest')->willReturn($response);

            $shortener = new TinyUrl($httpClient, $requestFactory);

            try {
                $shortener->shorten('https://example.com');
                $this->fail("Expected exception for status code $statusCode");
            } catch (UrlShortenerException $e) {
                $this->assertStringContainsString((string) $statusCode, $e->getMessage());
            }
        }
    }

    public function testShortenVerifiesUrlEncoding(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        // Capture the URL that was used
        $capturedUrl = null;
        $requestFactory->method('createRequest')
            ->willReturnCallback(function ($method, $url) use ($request, &$capturedUrl) {
                $capturedUrl = $url;
                return $request;
            });

        $response->method('getStatusCode')->willReturn(200);
        $stream->method('__toString')->willReturn('https://tinyurl.com/test');
        $response->method('getBody')->willReturn($stream);
        $httpClient->method('sendRequest')->willReturn($response);

        $shortener = new TinyUrl($httpClient, $requestFactory);
        // Use a URL with query parameters that need encoding
        $shortener->shorten('https://example.com/path?foo=bar&baz=qux');

        // Verify URL encoding happened in the request
        $this->assertNotNull($capturedUrl);
        $this->assertStringContainsString('url=', $capturedUrl);
        // Verify the URL was properly encoded as a query parameter
        $this->assertStringContainsString('https%3A%2F%2F', $capturedUrl);
    }

    public function testInterfaceCompliance(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);

        $shortener = new TinyUrl($httpClient, $requestFactory);

        $this->assertInstanceOf(\Horde\Service\UrlShortener\UrlShortenerInterface::class, $shortener);
    }
}
