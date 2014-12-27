<?php
namespace Haystack\Fields;
/**
 * Representation of a field in the search engine.
 *
 * @package Haystack\Fields
 */
abstract class Field
{
    /**
     * Data type integer.
     */
    const TYPE_INT = 'int';

    /**
     * Data type float.
     */
    const TYPE_FLOAT = 'float';

    /**
     * Data type string.
     */
    const TYPE_STRING = 'string';

    /**
     * Name of the field in the search engine index.
     *
     * @var string
     */
    protected $name;

    /**
     * Setting that tells Haystack to set the index schema to index this field.
     *
     * @var boolean
     */
    protected $indexed;

    /**
     * Setting that tells Haystack to set the index schema to store the value of this field.
     * @var boolean
     */
    protected $stored;

    /**
     * Reference to the engine that has instantiated this Field.
     *
     * @var \Haystack\Engines\Engine
     */
    protected $engine;

    /**
     * Arguments will be passed in by the Engine when creating Field objects.
     *
     * @param string $field_name Name of the field defined in the Haystack index.
     * @param array $args
     */
    public function __construct($field_name, array $args, \Haystack\Engines\Engine $engine)
    {
        $this->name = $field_name;
        $this->indexed = isset($args['indexed']) ? $args['indexed'] : true;
        $this->stored = isset($args['stored']) ? $args['stored'] : true;

        $this->engine = $engine;
    }

    /**
     * Returns this field in the necessary format when creating the index schema.
     *
     * @return mixed
     */
    abstract public function toSchema();

    /**
     * Type cast and/or cleans the data input as required by the Field type.
     *
     * @return void
     */
    abstract public function sanitize(&$input);
}