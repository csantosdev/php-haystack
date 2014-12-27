<?php
namespace Haystack\Engines\Elasticsearch\Fields;

use Haystack\Fields\Field;

class IntegerField extends Field
{
    /**
     * {@inheritdoc}
     */
    public function toSchema()
    {
        return array(
            $this->name => array('type' => 'integer', 'store' => $this->stored)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize(&$input)
    {
        if(!is_integer($input)) {
            $input = (int)$input;
        }
    }
}