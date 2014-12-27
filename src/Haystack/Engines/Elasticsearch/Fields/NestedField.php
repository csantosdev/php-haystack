<?php
namespace Haystack\Engines\Elasticsearch\Fields;

use Haystack\Fields\Field;

class NestedField extends Field
{
    protected $args;

    /**
     * {@inheritdoc}
     */
    public function __construct($field_name, array $args, \Haystack\Engines\Engine $engine)
    {
        parent::__construct($field_name, $args, $engine);

        $this->args = $args;
    }

    /**
     * {@inheritdoc}
     */
    public function toSchema()
    {
        if(!isset($this->args['fields'])) {
            throw new \Exception('The index "fields" must be provided when defining the NestedField field type.');
        }

        return $this->buildSchema($this->args['fields']);

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

    /**
     * Recursively builds the Elasticsearch mapping for this Nested field type.
     *
     * @param array $fields
     */
    private function buildSchema(array $fields)
    {
        $mapping = array();

        foreach($fields as $field_name => $field_value) {
            $field_class = $this->getFieldClass($field_value[0]);
            $field = new $field_class($field_name, $field_value, $this->engine);
            $schema[] = $field->toSchema();
        }

        return $mapping;
    }
}