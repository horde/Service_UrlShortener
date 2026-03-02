<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener\ValueObject;

use Stringable;

/**
 * Represents a shortened URL.
 *
 * Value object with validation and metadata.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
final readonly class ShortUrl implements Stringable
{
    private function __construct(
        private string $url,
        private string $service,
    ) {
        if (empty($url)) {
            throw new \InvalidArgumentException('Short URL cannot be empty');
        }
    }

    public static function create(string $url, string $service): self
    {
        return new self($url, $service);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getService(): string
    {
        return $this->service;
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
     * Get the short code (e.g., "abc123" from "https://tinyurl.com/abc123").
     */
    public function getCode(): string
    {
        $path = parse_url($this->url, PHP_URL_PATH);
        return $path ? ltrim($path, '/') : '';
    }

    /**
     * Get the domain of the shortening service.
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
}
