<?php

/**
 * PSR-0 backward compatibility wrapper for Horde\Service\UrlShortener\TinyUrl.
 *
 * Copyright 2011-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @deprecated Use Horde\Service\UrlShortener\TinyUrl instead. Will be removed in Horde 7.
 * @package  Service_UrlShortener
 */
class Horde_Service_UrlShortener_TinyUrl extends Horde_Service_UrlShortener_Base
{
    /**
     * Modern PSR-4 implementation (lazy-loaded).
     *
     * @var \Horde\Service\UrlShortener\TinyUrl|null
     */
    private $_modernImpl;

    /**
     * Shorten a URL using TinyUrl.
     *
     * @param string $url  The URL to shorten
     *
     * @return string  The shortened URL
     * @throws Horde_Service_UrlShortener_Exception
     */
    public function shorten($url)
    {
        if ($this->_modernImpl === null) {
            // Convert legacy Horde_Http_Client to PSR-18
            $psr18Client = $this->_convertToPsr18Client();
            $requestFactory = $this->_getRequestFactory();

            $this->_modernImpl = new \Horde\Service\UrlShortener\TinyUrl(
                $psr18Client,
                $requestFactory,
                \Horde\Service\UrlShortener\ValueObject\ShortenerConfig::default()
            );
        }

        try {
            return $this->_modernImpl->shorten((string) $url);
        } catch (\Horde\Service\UrlShortener\UrlShortenerException $e) {
            // Wrap PSR-4 exception as PSR-0 exception
            throw new Horde_Service_UrlShortener_Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Convert legacy Horde_Http_Client to PSR-18 ClientInterface.
     *
     * @return \Psr\Http\Client\ClientInterface
     */
    private function _convertToPsr18Client(): \Psr\Http\Client\ClientInterface
    {
        // Check if Horde_Http_Client already implements PSR-18
        if ($this->_http instanceof \Psr\Http\Client\ClientInterface) {
            return $this->_http;
        }

        // Wrap legacy client in adapter
        return new \Horde\Service\UrlShortener\LegacyHttpClientAdapter($this->_http);
    }

    /**
     * Get PSR-17 RequestFactory.
     *
     * @return \Psr\Http\Message\RequestFactoryInterface
     */
    private function _getRequestFactory(): \Psr\Http\Message\RequestFactoryInterface
    {
        // Use Horde\Http PSR-7 implementation if available
        if (class_exists('\\Horde\\Http\\Psr7\\RequestFactory')) {
            return new \Horde\Http\Psr7\RequestFactory();
        }

        // Fallback to any available PSR-17 implementation
        foreach ([
            '\\Nyholm\\Psr7\\Factory\\Psr17Factory',
            '\\GuzzleHttp\\Psr7\\HttpFactory',
            '\\Laminas\\Diactoros\\RequestFactory',
        ] as $factoryClass) {
            if (class_exists($factoryClass)) {
                return new $factoryClass();
            }
        }

        throw new Horde_Service_UrlShortener_Exception(
            'No PSR-17 RequestFactory implementation found. Install horde/http, nyholm/psr7, or guzzlehttp/psr7.'
        );
    }
}
