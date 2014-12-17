<?php
namespace Indexes;

class Product
{
    public $name = array('type' => 'string');
    public $price = array('type' => 'string');
    public $category = array('type' => 'string');

    public function getIndexName()
    {
        return 'product';
    }

    public function getModelName()
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