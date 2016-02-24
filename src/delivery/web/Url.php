<?php
namespace rtens\domin\delivery\web;

class Url {

    const HOST_PREFIX = '//';
    const PORT_SEPARATOR = ':';
    const SCHEME_SEPARATOR = ':';
    const QUERY_STRING_SEPARATOR = '?';
    const FRAGMENT_SEPARATOR = '#';
    const MAX_PARAM_LENGTH = 512;

    /** @var null|string */
    private $scheme;

    /** @var null|string */
    private $host;

    /** @var null|int */
    private $port;

    /** @var array */
    private $path;

    /** @var array */
    private $parameters;

    /** @var string|null */
    private $fragment;

    /**
     * @param string $scheme
     * @param string $host
     * @param int $port
     * @param array $path
     * @param array $parameters
     * @param string|null $fragment
     */
    function __construct($scheme = 'http', $host = null, $port = 80, array $path = [], $parameters = [], $fragment = null) {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->parameters = $parameters;
        $this->fragment = $fragment;
    }

    /**
     * @param array|string $path
     * @param array $parameters
     * @param null|string $fragment
     * @return Url
     */
    public static function relative($path, array $parameters = [], $fragment = null) {
        return new Url(null, null, null, (array)$path, $parameters, $fragment);
    }

    /**
     * @return Url
     */
    private function copy() {
        return new Url(
            $this->scheme,
            $this->host,
            $this->port,
            $this->path,
            $this->parameters,
            $this->fragment
        );
    }

    /**
     * @return null|string
     */
    public function getScheme() {
        return $this->scheme;
    }
    /**
     * @return null|string
     */
    public function getHost() {
        return $this->host;
    }
    /**
     * @return int|null
     */
    public function getPort() {
        return $this->port;
    }
    /**
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return static
     */
    public function withParameters(array $parameters) {
        $newUrl = $this->copy();
        $newUrl->parameters = $parameters;
        return $newUrl;
    }
    /**
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function withParameter($key, $value) {
        $newUrl = $this->copy();
        $newUrl->parameters[$key] = $value;
        return $newUrl;
    }
    /**
     * @return null|string
     */
    public function getFragment() {
        return $this->fragment;
    }
    /**
     * @return array
     */
    public function getPath() {
        return $this->path;
    }
    /**
     * @param array $path
     * @return static
     */
    public function withPath(array $path) {
        $url = $this->copy();
        $url->path = $path;
        return $url;
    }

    /**
     * @param array $path
     * @return Url
     */
    public function append(array $path) {
        $url = $this->copy();
        $url->path = array_merge($url->path, $path);
        return $url;
    }

    public function __toString() {
        $queries = array();
        foreach ($this->flattenParams($this->parameters) as $key => $value) {
            $queries[] = $key . '=' . urlencode($value);
        }
        $port = $this->port ? self::PORT_SEPARATOR . $this->port : '';
        $scheme = $this->scheme ? $this->scheme . self::SCHEME_SEPARATOR : '';
        $server = $this->host ? $scheme . self::HOST_PREFIX . $this->host . $port : '';
        return
            $server
            . implode('/', $this->path)
            . ($queries ? self::QUERY_STRING_SEPARATOR . implode('&', $queries) : '')
            . ($this->fragment ? self::FRAGMENT_SEPARATOR . $this->fragment : '');
    }

    private function flattenParams($parameters, $i = 0) {
        $flat = [];
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                foreach ($this->flattenParams($value, $i + 1) as $subKey => $subValue) {
                    $flatKey = $i ? "{$key}][{$subKey}" : "{$key}[{$subKey}]";
                    $flat = $this->set($flat, $flatKey, $subValue);
                }
            } else {
                $flat = $this->set($flat, $key, $value);
            }
        }
        return $flat;
    }

    private function set(array $map, $key, $value) {
        $cabBeCasted = !is_object($value) || method_exists($value, '__toString');
        if ($cabBeCasted && strlen((string)$value) <= self::MAX_PARAM_LENGTH) {
            $map[$key] = $value;
        }
        return $map;
    }
}