<?php
namespace Haystack\Engines\Elasticsearch\Fields;

use Haystack\Fields\Field;

class ArrayField extends Field
{
    protected $type;

    /**
     * {@inheritdoc}
     */
    public function __construct($field_name, array $args, \Haystack\Engines\Engine $engine)
    {
        parent::__construct($field_name, $args, $engine);

        $this->type = isset($args['type']) ? $args['type'] : 'string';
    }

    /**
     * {@inheritdoc}
     */
    public function toSchema()
    {
        return array('type' => 'string', 'store' => $this->stored);
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize(&$input)
    {
        switch($this->type) {

            case Field::TYPE_INT:

                if(!is_integer($input)) {
                    $input = (int)$input;
                }
                break;

            case Field::TYPE_FLOAT:

                if(!is_float($input)) {
                    $input = (float)$input;
                }
                break;

            case Field::TYPE_STRING:

                if(!is_string($input)) {
                    $input = (string)$input;
                }
                break;
        }
    }
}