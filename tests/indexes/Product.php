<?php
namespace Indexes;

use Haystack\Fields\FieldFactory;
use Haystack\Fields\Field;

class Product extends \Haystack\Index
{
    public $name = array('CharField');
    public $price = array('FloatField');
    public $categories = array('ArrayField', 'type' => Field::TYPE_INT);
    public $meta = array(
        'NestedField',
        'fields' => array(
            'meta_name' => array('CharField'),
            'meta_value' => array('CharField'),
            'meta_options' => array(
                'NestedField',
                'fields' => array(
                    'tags' => array('ArrayField', 'type' => FIELD::TYPE_STRING),
                    'value' => array('CharField')
                )
            )
        )
    );

    public function getIndexName()
    {
        return 'product';
    }

    public function getModelClass()
    {
        return 'Models\Product';
    }

    /**
     * Type within the index.
     * This is an Elasticsearch thing.
     */
    public function getTypeName()
    {
        return 'default';
    }

    public function prepare_category($instance)
    {
        if (mt_rand(0, 1) === 0) {
            return 'Electronics';

        } else {
            return 'Video Games';
        }
    }
}