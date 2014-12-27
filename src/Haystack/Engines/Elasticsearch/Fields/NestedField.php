<?php
namespace Haystack\Engines\Elasticsearch\Fields;

use Haystack\Fields\Field;

class NestedField extends Field
{
    protected $type;

    /**
     * {@inheritdoc}
     */
    public function __construct($field_name, array $args, \Haystack\Engines\Engine $engine)
    {
        parent::__construct($field_name, $args, $engine);

        if(!isset($args['fields'])) {
            throw new \Exception('The index "fields" must be provided when defining the NestedField field type.');
        }



        $this->type = isset($args['']) ? $args[''] : 'string';
    }

    /**
     * {@inheritdoc}
     */
    public function toSchema()
    {
        return array(
            $this->name => array('type' => 'string', 'store' => $this->stored)
        );
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