<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener\Test\Unit\ValueObject;

use Horde\Service\UrlShortener\ValueObject\ShortenerConfig;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ShortenerConfig value object.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
class ShortenerConfigTest extends TestCase
{
    public function testDefaultConstruction(): void
    {
        $config = new ShortenerConfig();

        $this->assertEquals(10, $config->timeout);
        $this->assertTrue($config->verifySSL);
        $this->assertNull($config->customAlias);
        $this->assertNull($config->expirationDays);
    }

    public function testCustomConstruction(): void
    {
        $config = new ShortenerConfig(30, false, 'myalias', 7);

        $this->assertEquals(30, $config->timeout);
        $this->assertFalse($config->verifySSL);
        $this->assertEquals('myalias', $config->customAlias);
        $this->assertEquals(7, $config->expirationDays);
    }

    public function testDefaultFactory(): void
    {
        $config = ShortenerConfig::default();

        $this->assertInstanceOf(ShortenerConfig::class, $config);
        $this->assertEquals(10, $config->timeout);
    }

    public function testWithTimeout(): void
    {
        $config = ShortenerConfig::default();
        $newConfig = $config->withTimeout(60);

        // Original unchanged (immutable)
        $this->assertEquals(10, $config->timeout);

        // New config has new timeout
        $this->assertEquals(60, $newConfig->timeout);
        $this->assertTrue($newConfig->verifySSL);
    }

    public function testWithoutSSLVerification(): void
    {
        $config = ShortenerConfig::default();
        $newConfig = $config->withoutSSLVerification();

        // Original unchanged
        $this->assertTrue($config->verifySSL);

        // New config has SSL disabled
        $this->assertFalse($newConfig->verifySSL);
    }

    public function testWithCustomAlias(): void
    {
        $config = ShortenerConfig::default();
        $newConfig = $config->withCustomAlias('myshorturl');

        // Original unchanged
        $this->assertNull($config->customAlias);

        // New config has alias
        $this->assertEquals('myshorturl', $newConfig->customAlias);
    }

    public function testWithExpiration(): void
    {
        $config = ShortenerConfig::default();
        $newConfig = $config->withExpiration(14);

        // Original unchanged
        $this->assertNull($config->expirationDays);

        // New config has expiration
        $this->assertEquals(14, $newConfig->expirationDays);
    }

    public function testFluentChaining(): void
    {
        $config = ShortenerConfig::default()
            ->withTimeout(45)
            ->withoutSSLVerification()
            ->withCustomAlias('test')
            ->withExpiration(30);

        $this->assertEquals(45, $config->timeout);
        $this->assertFalse($config->verifySSL);
        $this->assertEquals('test', $config->customAlias);
        $this->assertEquals(30, $config->expirationDays);
    }

    public function testImmutability(): void
    {
        $config1 = new ShortenerConfig(10, true, null, null);
        $config2 = $config1->withTimeout(20);
        $config3 = $config2->withCustomAlias('alias');

        // Each is independent
        $this->assertEquals(10, $config1->timeout);
        $this->assertEquals(20, $config2->timeout);
        $this->assertEquals(20, $config3->timeout);
        $this->assertNull($config1->customAlias);
        $this->assertNull($config2->customAlias);
        $this->assertEquals('alias', $config3->customAlias);
    }
}
