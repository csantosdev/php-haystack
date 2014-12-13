<?php
namespace Haystack;
/**
 * Class Haystack
 *
 * @package Haystack
 * @author Chris Santos <csantosdev@gmail.com>
 */
class Haystack
{
    /**
     * @var array
     */
    private static $instances;

    /**
     * Map of search engine configurations.
     *
     * @var array
     */
    private static $config;

    /**
     * Set configurations for all engines.
     *
     * @param array $conf
     * @param string $default
     */
    public static function setConfiguration($conf)
    {
        self::$config = $conf;
    }

    /**
     * Returns an instance of a Haystack engine.
     *
     * @param string $conf Configuration name.
     * @return \Haystack\Engines\Engine
     * @throws \Exception
     */
    public static function getEngine($name = 'default')
    {
        if (!isset(self::$conf[$name])) {
            throw new \Exception("There is no configuration '$name' set for Haystack.");
        }

        if (!isset(self::$instances[$name])) {
            $conf = self::$config[$name];
            self::$instances[$conf] = new $conf['engine']($conf);
        }

        return self::$instances[$name];
    }
}