<?php
/**
 * Tests creating, updating and deleting indexes.
 *
 * @author Chris Santos <csantosdev@gmail.com>
 */
use Haystack\Haystack;

class IndexingTest extends PHPUnit_Framework_TestCase
{

    private $haystack;

    public function setUp()
    {
        $conf = [
            'default' => [
                'engine' => '\Haystack\Engines\Elasticsearch',
                'host' => 'localhost'
            ]
        ];

        Haystack::setConfiguration($conf);

        $this->haystack = Haystack::getEngine();
    }

    public function tearDown()
    {
        // Remove data from store
    }

    /**
     * Tests the creation of an index.
     */
    public function createIndexTest()
    {
        $this->haystack->
    }

    /**
     * Tests the deletion of an index.
     */
    public function deleteIndexTest()
    {

    }

    /**
     * Tests updating an existing index.
     */
    public function updateIndexTest()
    {

    }
}