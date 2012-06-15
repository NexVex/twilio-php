<?php

/**
 * Abstraction of a Twilio resource.
 *
 * @category Services
 * @package  Services_Twilio
 * @author   Neuman Vong <neuman@twilio.com>
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 * @link     http://pear.php.net/package/Services_Twilio
 */ 
abstract class Services_Twilio_Resource
{
    protected $name;
    protected $proxy;
    protected $subresources;

    public function __construct($client, $uri, $params = array())
    {
        $this->subresources = array();
        $this->name = get_class($this);
        $this->client = $client;
        $this->uri = $uri;

        foreach ($params as $name => $param) {
            $this->$name = $param;
        }
        $this->init($client, $uri);
    }

    protected function init($client, $uri)
    {
        // Left empty for derived classes to implement
    }

    public function getSubresources($name = null)
    {
        if (isset($name)) {
            return isset($this->subresources[$name])
                ? $this->subresources[$name]
                : null;
        }
        return $this->subresources;
    }

    public function addSubresource($name, Services_Twilio_Resource $res)
    {
        $this->subresources[$name] = $res;
    }

    protected function setupSubresources()
    {
        foreach (func_get_args() as $name) {
            $constantized = ucfirst(Services_Twilio_Resource::camelize($name));
            $type = "Services_Twilio_Rest_" . $constantized;
            $this->addSubresource($name, new $type($this->client, $this->uri . "/$constantized"));
        }
    }

    public function getResourceName($camelized = false) 
    {
        $name = get_class($this);
        $parts = explode('_', $name);
        $basename = end($parts);
        if ($camelized) {
            return $basename;
        } else {
            return self::decamelize($basename);
        }
    }

    public static function decamelize($word)
    {
        return preg_replace(
            '/(^|[a-z])([A-Z])/e',
            'strtolower(strlen("\\1") ? "\\1_\\2" : "\\2")',
            $word
        );
    }

    public static function camelize($word)
    {
        return preg_replace('/(^|_)([a-z])/e', 'strtoupper("\\2")', $word);
    }
}

