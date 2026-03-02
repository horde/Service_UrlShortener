<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener;

use Horde\Service\UrlShortener\ValueObject\LongUrl;
use Horde\Service\UrlShortener\ValueObject\ShortUrl;
use Horde\Service\UrlShortener\ValueObject\ShorteningResult;
use Horde\Service\UrlShortener\ValueObject\ShortenerConfig;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriInterface;
use DateTimeImmutable;

/**
 * TinyUrl URL shortening service.
 *
 * Implements both facade API and rich domain API using native PSR-7/PSR-17.
 *
 * Copyright 2011-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
final class TinyUrl implements UrlShortenerInterface
{
    private const API_URL = 'http://tinyurl.com/api-create.php';
    private const SERVICE_NAME = 'TinyUrl';

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly ShortenerConfig $config = new ShortenerConfig(),
    ) {}

    /**
     * FACADE API - Simple string-based shortening.
     *
     * {@inheritdoc}
     */
    public function shorten(string|UriInterface $url): string
    {
        // Convert to rich type, call domain API, extract string
        $longUrl = LongUrl::fromString((string) $url);
        $result = $this->shortenWithMetadata($longUrl);
        return $result->getShortUrl()->toString();
    }

    /**
     * DOMAIN API - Rich typed shortening with metadata.
     *
     * {@inheritdoc}
     */
    public function shortenWithMetadata(LongUrl $url): ShorteningResult
    {
        $shortUrlString = $this->callApiAndGetShortUrl($url);

        $shortUrl = ShortUrl::create($shortUrlString, self::SERVICE_NAME);

        return new ShorteningResult(
            originalUrl: $url,
            shortUrl: $shortUrl,
            createdAt: new DateTimeImmutable(),
            customAlias: $this->config->customAlias,
            expiresAt: $this->config->expirationDays
                ? (new DateTimeImmutable())->modify("+{$this->config->expirationDays} days")
                : null,
        );
    }

    public function getServiceName(): string
    {
        return self::SERVICE_NAME;
    }

    /**
     * Internal: Make the actual API call using native PSR-7/PSR-17.
     */
    private function callApiAndGetShortUrl(LongUrl $url): string
    {
        // Build request URL with query parameters
        $requestUrl = self::API_URL . '?url=' . urlencode($url->toString());

        // Create PSR-7 request using native PSR-17 factory
        $request = $this->requestFactory->createRequest('GET', $requestUrl);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Psr\Http\Client\ClientExceptionInterface $e) {
            throw new UrlShortenerException(
                'Failed to shorten URL via TinyUrl: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        if ($response->getStatusCode() !== 200) {
            throw new UrlShortenerException(
                sprintf(
                    'TinyUrl API returned status %d: %s',
                    $response->getStatusCode(),
                    $response->getReasonPhrase()
                )
            );
        }

        $body = (string) $response->getBody();

        if (empty($body)) {
            throw new UrlShortenerException('TinyUrl API returned empty response');
        }

        return trim($body);
    }
}
