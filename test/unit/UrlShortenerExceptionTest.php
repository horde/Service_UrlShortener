<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener\Test\Unit;

use Horde\Service\UrlShortener\UrlShortenerException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for UrlShortenerException.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 */
class UrlShortenerExceptionTest extends TestCase
{
    public function testBasicException(): void
    {
        $exception = new UrlShortenerException('Test message');

        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testExceptionWithCode(): void
    {
        $exception = new UrlShortenerException('Test message', 500);

        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new UrlShortenerException('Wrapped exception', 0, $previous);

        $this->assertEquals('Wrapped exception', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionInheritance(): void
    {
        $exception = new UrlShortenerException('Test');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(UrlShortenerException::class);
        $this->expectExceptionMessage('Thrown exception');

        throw new UrlShortenerException('Thrown exception');
    }
}
