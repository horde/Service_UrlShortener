<?php

/**
 * PSR-0 backward compatibility wrapper for Horde\Service\UrlShortener.
 *
 * This maintains the legacy mutable API while delegating to the modern
 * PSR-4 implementation.
 *
 * Copyright 2011-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @deprecated Use Horde\Service\UrlShortener\* instead. Will be removed in Horde 7.
 * @package  Service_UrlShortener
 */
abstract class Horde_Service_UrlShortener_Base
{
    /**
     * @var array
     */
    protected $_params;

    /**
     * @var Horde_Http_Client
     */
    protected $_http;

    /**
     * Constructor
     *
     * @param Horde_Http_Client $http
     * @param array $params
     */
    public function __construct(Horde_Http_Client $http, $params = array())
    {
        $this->_http = $http;
        $this->_params = $params;
    }

    /**
     * Shorten a URL.
     *
     * @param string $url  The URL to shorten
     *
     * @return string  The shortened URL
     * @throws Horde_Service_UrlShortener_Exception
     */
    abstract public function shorten($url);
}
