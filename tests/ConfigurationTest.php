<?php
use Haystack\Haystack;
use Models\Product as Product;
use Indexes\Product as ProductIndex;

class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Haystack\Engines\Engine
     */
    private $haystack;

    public function setUp()
    {
        $conf = array(
            'default' => array(
                'engine' => '\Haystack\Engines\Elasticsearch',
                'host' => 'localhost'
            )
        );

        Haystack::setConfiguration($conf);

        $this->haystack = Haystack::getEngine();
    }

    public function tearDown()
    {

    }

    public function testIndexCreation()
    {
        $this->haystack->createIndex('Indexes\Product');

        $this->assertTrue($this->haystack->indexExists('Indexes\Product'));
    }

    public function testIndexDeletion()
    {
        $this->haystack->deleteIndex('Indexes\Product');

        $this->assertFalse($this->haystack->indexExists('Indexes\Product'));
    }
}