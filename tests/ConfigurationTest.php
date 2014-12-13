<?php
use Haystack\Haystack;

class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once '../indexes/ProductIndex';

        $conf = [
            'default' => [
                'engine' => '\Haystack\Engines\Elasticsearch',
                'host' => 'localhost'
            ]
        ];

        Haystack::setConfiguration($conf);

        $haystack = Haystack::getEngine();
        $index = $haystack->getIndexInstance('ProductIndex');

        var_dump($index);
    }

    public function tearDown()
    {
        // Remove data from store
    }

    public function testCan()
    {
        $this->assertEquals(1, 1);
    }
}