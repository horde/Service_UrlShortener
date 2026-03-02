<?php

declare(strict_types=1);

namespace Horde\Service\UrlShortener;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Adapter to make legacy Horde_Http_Client work as PSR-18 ClientInterface.
 *
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Service_UrlShortener
 * @internal
 */
final class LegacyHttpClientAdapter implements ClientInterface
{
    public function __construct(
        private readonly \Horde_Http_Client $legacyClient,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            // Extract URL from PSR-7 request
            $url = (string) $request->getUri();

            // Call legacy client's get method
            $legacyResponse = $this->legacyClient->get($url);

            // Convert legacy response to PSR-7 response
            return $this->convertResponse($legacyResponse);
        } catch (\Horde_Http_Exception $e) {
            throw new \RuntimeException(
                'HTTP request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Convert legacy Horde_Http_Response to PSR-7 ResponseInterface.
     */
    private function convertResponse(\Horde_Http_Response $legacyResponse): ResponseInterface
    {
        // Find available PSR-7 response factory
        $response = $this->createPsr7Response(
            $legacyResponse->code,
            $legacyResponse->getBody()
        );

        return $response;
    }

    /**
     * Create PSR-7 response using available factory.
     */
    private function createPsr7Response(int $statusCode, string $body): ResponseInterface
    {
        // Try Horde\Http first
        if (class_exists('\\Horde\\Http\\Psr7\\Response')) {
            $response = new \Horde\Http\Psr7\Response();
            $response = $response->withStatus($statusCode);
            $response = $response->withBody(
                \Horde\Http\Psr7\Stream::create($body)
            );
            return $response;
        }

        // Try other PSR-7 implementations
        foreach ([
            ['\\Nyholm\\Psr7\\Response', '\\Nyholm\\Psr7\\Stream'],
            ['\\GuzzleHttp\\Psr7\\Response', '\\GuzzleHttp\\Psr7\\Utils'],
            ['\\Laminas\\Diactoros\\Response', '\\Laminas\\Diactoros\\Stream'],
        ] as [$responseClass, $streamClass]) {
            if (class_exists($responseClass)) {
                if ($responseClass === '\\GuzzleHttp\\Psr7\\Response') {
                    return new $responseClass($statusCode, [], $body);
                }

                $stream = $this->createStream($body, $streamClass);
                return new $responseClass($stream, $statusCode);
            }
        }

        throw new \RuntimeException(
            'No PSR-7 Response implementation found. Install horde/http, nyholm/psr7, or guzzlehttp/psr7.'
        );
    }

    /**
     * Create stream from string body.
     */
    private function createStream(string $body, string $streamClass): mixed
    {
        if ($streamClass === '\\Horde\\Http\\Psr7\\Stream') {
            return \Horde\Http\Psr7\Stream::create($body);
        }

        if ($streamClass === '\\Nyholm\\Psr7\\Stream') {
            return \Nyholm\Psr7\Stream::create($body);
        }

        if ($streamClass === '\\GuzzleHttp\\Psr7\\Utils') {
            return \GuzzleHttp\Psr7\Utils::streamFor($body);
        }

        if ($streamClass === '\\Laminas\\Diactoros\\Stream') {
            $stream = new \Laminas\Diactoros\Stream('php://temp', 'wb+');
            $stream->write($body);
            $stream->rewind();
            return $stream;
        }

        throw new \RuntimeException("Unknown stream class: $streamClass");
    }
}
