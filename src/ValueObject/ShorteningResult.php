<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener\ValueObject;

use DateTimeImmutable;

/**
 * Complete result of a URL shortening operation.
 *
 * Includes the shortened URL plus metadata about the operation.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
final readonly class ShorteningResult
{
    public function __construct(
        private LongUrl $originalUrl,
        private ShortUrl $shortUrl,
        private DateTimeImmutable $createdAt,
        private ?string $customAlias = null,
        private ?DateTimeImmutable $expiresAt = null,
    ) {}

    public function getOriginalUrl(): LongUrl
    {
        return $this->originalUrl;
    }

    public function getShortUrl(): ShortUrl
    {
        return $this->shortUrl;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCustomAlias(): ?string
    {
        return $this->customAlias;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * Check if this short URL will expire.
     */
    public function hasExpiration(): bool
    {
        return $this->expiresAt !== null;
    }

    /**
     * Check if this short URL has expired.
     */
    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt < new DateTimeImmutable();
    }

    /**
     * Calculate length reduction (useful for metrics).
     */
    public function getLengthReduction(): int
    {
        return strlen((string) $this->originalUrl) - strlen((string) $this->shortUrl);
    }

    /**
     * Calculate percentage saved.
     */
    public function getPercentageSaved(): float
    {
        $original = strlen((string) $this->originalUrl);
        if ($original === 0) {
            return 0.0;
        }

        return (1 - (strlen((string) $this->shortUrl) / $original)) * 100;
    }
}
