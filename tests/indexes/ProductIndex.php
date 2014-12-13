<?php
class ProductIndex
{
    public $name = [];
    public $price = [];
    public $category = [];

    public function prepare_category($instance)
    {
        if (mt_rand(0, 1) === 0) {
            return 'Electronics';

        } else {
            return 'Video Games';
        }
    }

    public function getModelName()
    {
        return '\Product';
    }
}