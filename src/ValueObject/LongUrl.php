<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener\ValueObject;

use Horde\Url\Url;
use Psr\Http\Message\UriInterface;
use Stringable;

/**
 * Represents a URL to be shortened.
 *
 * Value object ensuring URL validity.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
final readonly class LongUrl implements Stringable
{
    private string $url;

    private function __construct(string $url)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('URL cannot be empty');
        }

        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL: $url");
        }

        $this->url = $url;
    }

    /**
     * Create from string.
     */
    public static function fromString(string $url): self
    {
        return new self($url);
    }

    /**
     * Create from Horde\Url\Url.
     */
    public static function fromHordeUrl(Url $url): self
    {
        return new self($url->toString(raw: true));
    }

    /**
     * Create from PSR-7 URI.
     */
    public static function fromPsr7Uri(UriInterface $uri): self
    {
        return new self((string) $uri);
    }

    public function toString(): string
    {
        return $this->url;
    }

    public function __toString(): string
    {
        return $this->url;
    }

    /**
     * Get the domain from the URL.
     */
    public function getDomain(): string
    {
        return parse_url($this->url, PHP_URL_HOST) ?? '';
    }

    /**
     * Get the scheme (http/https).
     */
    public function getScheme(): string
    {
        return parse_url($this->url, PHP_URL_SCHEME) ?? '';
    }

    /**
     * Check if URL is HTTPS.
     */
    public function isSecure(): bool
    {
        return $this->getScheme() === 'https';
    }

    /**
     * Get the full URL path.
     */
    public function getPath(): string
    {
        return parse_url($this->url, PHP_URL_PATH) ?? '';
    }

    /**
     * Get query string.
     */
    public function getQuery(): string
    {
        return parse_url($this->url, PHP_URL_QUERY) ?? '';
    }
}
