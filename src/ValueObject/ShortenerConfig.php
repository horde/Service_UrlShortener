<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener\ValueObject;

/**
 * Configuration for URL shortening operations.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
final readonly class ShortenerConfig
{
    public function __construct(
        public int $timeout = 10,
        public bool $verifySSL = true,
        public ?string $customAlias = null,
        public ?int $expirationDays = null,
    ) {}

    public static function default(): self
    {
        return new self();
    }

    public function withTimeout(int $timeout): self
    {
        return new self($timeout, $this->verifySSL, $this->customAlias, $this->expirationDays);
    }

    public function withoutSSLVerification(): self
    {
        return new self($this->timeout, false, $this->customAlias, $this->expirationDays);
    }

    public function withCustomAlias(string $alias): self
    {
        return new self($this->timeout, $this->verifySSL, $alias, $this->expirationDays);
    }

    public function withExpiration(int $days): self
    {
        return new self($this->timeout, $this->verifySSL, $this->customAlias, $days);
    }
}
