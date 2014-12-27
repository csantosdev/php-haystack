<?php
/**
 * Tests the functionality of creating, updating and deleting indexes.
 *
 * @author Chris Santos <csantosdev@gmail.com>
 */
class ConfigurationTest extends Base
{
    public function testIndexCreation()
    {
        self::$haystack->createIndex('Indexes\Product');

        $this->assertTrue(self::$haystack->indexExists('Indexes\Product'));
    }

    public function testIndexUpdate()
    {

    }

    public function testIndexDeletion()
    {
        //self::$haystack->deleteIndex('Indexes\Product');

        //$this->assertFalse(self::$haystack->indexExists('Indexes\Product'));
    }
}