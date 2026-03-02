<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener;

use Horde\Service\UrlShortener\ValueObject\LongUrl;
use Horde\Service\UrlShortener\ValueObject\ShorteningResult;
use Psr\Http\Message\UriInterface;

/**
 * URL shortening service interface.
 *
 * Provides both facade API (simple) and rich domain API (typed).
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
interface UrlShortenerInterface
{
    /**
     * FACADE API - Simple, accepts fundamental types.
     *
     * Shorten a URL using basic types.
     *
     * @param string|UriInterface $url  The URL to shorten
     *
     * @return string  The shortened URL
     * @throws UrlShortenerException
     */
    public function shorten(string|UriInterface $url): string;

    /**
     * DOMAIN API - Rich, uses value objects.
     *
     * Shorten a URL with full type safety and metadata.
     *
     * @param LongUrl $url  The URL to shorten
     *
     * @return ShorteningResult  Complete result with metadata
     * @throws UrlShortenerException
     */
    public function shortenWithMetadata(LongUrl $url): ShorteningResult;

    /**
     * Get the service name (for identification).
     *
     * @return string  Service name (e.g., "TinyUrl", "Bitly")
     */
    public function getServiceName(): string;
}
