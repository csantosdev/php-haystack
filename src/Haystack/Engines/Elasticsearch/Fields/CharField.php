<?php
namespace Haystack\Engines\Elasticsearch\Fields;

use Haystack\Fields\Field;

class CharField extends Field
{
    /**
     * {@inheritdoc}
     */
    public function toSchema()
    {
        return array('type' => 'string', 'store' => $this->stored, 'test' => 'ok', 'index' => $this->indexed);
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize(&$input)
    {
        if(!is_string($input)) {
            $input = (string)$input;
        }
    }
}