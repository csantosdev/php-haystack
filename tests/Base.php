<?php
use Haystack\Haystack;

class Base extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Haystack\Engines\Engine
     */
    protected static $haystack;

    public static function setUpBeforeClass()
    {
        global $argv;

        if(!isset($argv[2])) {
            exit("You must provide a search engine type (Elasticsearch or Solr" . PHP_EOL);
        }

        $conf = array(
            'default' => array(
                'engine' => sprintf('\Haystack\Engines\%s', $argv[2]),
                'host' => 'localhost'
            )
        );

        Haystack::setConfiguration($conf);
        self::$haystack = Haystack::getEngine();
    }
}