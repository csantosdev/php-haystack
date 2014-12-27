<?php
/**
 * Tests creating, updating and deleting indexes.
 *
 * @author Chris Santos <csantosdev@gmail.com>
 */
use Haystack\Haystack;

class IndexingTest extends Base
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if(self::$haystack->indexExists('Indexes\Product')) {
            self::$haystack->deleteIndex('Indexes\Product');
        }

        self::$haystack->createIndex('Indexes\Product');
    }

    public static function tearDownAfterClass()
    {
        self::$haystack->deleteIndex('Indexes\Product');
    }

    /**
     * Tests the creation of a new document.
     */
    public function testIndexingNewDocument()
    {
        $document = array(
            'name' => 'Xbox',
            'price' => 300.00,
            'specs' => array(
                'width' => 100,
                'height' => 50
            )
        );
        $success = self::$haystack->indexDocument('Indexes\Product', $document);
        $this->assertTrue($success);

        $stored_document = self::$haystack->filter('Indexes\Product', array(
            'name' => 'Xbox',
            'price' => 300.00
        ));
        $this->assertSame($document, $stored_document->toArray());
    }

    /**
     * Tests the updating of an existing document.
     */
    public function testUpdatingDocument()
    {
        $document = array(
            'name' => 'Xbox',
            'price' => 400.00,
            'specs' => array(
                'width' => 100,
                'height' => 50
            )
        );
        $success = self::$haystack->updateDocument('Indexes\Product', $document);
        $this->assertTrue($success);

        $stored_document = self::$haystack->filter('Indexes\Product', array(
            'name' => 'Xbox',
            'price' => 300.00
        ));

        $this->assertSame($document, $stored_document->toArray());
    }

    /**
     * Tests the deletion of an existing document.
     */
    public function testDeleteDocument()
    {

    }
}