<?php

namespace ENV;

/**
 * Class ENV
 * @package ENV
 */
class ENV
{
    protected $directory;
    protected $loader;

    /**
     * ENV constructor.
     * @param $directory
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
        $this->loader = new Loader($directory);
    }

    /**
     *
     */
    public function load()
    {
        $this->loader->load();
        $_SERVER = array_merge($_SERVER, $this->loader->variables);
        $_ENV = array_merge($_ENV, $this->loader->variables);
    }

    /**
     * @param $key
     * @return string
     */
    public function get($key): string
    {
        $value = $this->loader->variables[$key];

        if (!is_string($value)) {
            array(1,2);
            // throw some weird exception
        }

        return $value;
    }

}