<?php
namespace Haystack\Engines\Elasticsearch\Fields;

use Haystack\Fields\Field;

class FloatField extends Field
{
    /**
     * {@inheritdoc}
     */
    public function toSchema()
    {
        return array('type' => 'float', 'store' => $this->stored);
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize(&$input)
    {
        if(!is_float($input)) {
            $input = (float)$input;
        }
    }
}