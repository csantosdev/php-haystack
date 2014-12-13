<?php
namespace Haystack\Engines;

abstract class Engine
{
    private $reflection_classes;

    /**
     * Map of instances of the loaded index classes.
     *
     * @var array
     */
    private $instances;

    private $index_map;

    /**
     * Map of index classes.
     *
     * @var array
     */
    private $index_classes = [];

    /**
     * Haystack index configuration classes are loaded when instantiating
     * an engine.
     *
     * @param array $conf
     */
    public function __construct($conf)
    {
        $this->index_classes = $conf['indexes'];
    }

    public function getIndexNameByModel($name)
    {
        if (!isset($this->index_classes[$name])) {
            throw new \Exception('There is no index for model name: ' . $name);
        }

        return $this->index_map[$name];
    }

    public function getIndexName($name)
    {

        $reflection = $this->getIndexReflectionClass($name);

        if ($reflection->hasMethod('getIndexName')) {
            return $this->getIndexInstance($name)->getIndexName();
        }

        return $name;
    }

    public function getIndexReflectionClass($name)
    {

        if (isset($this->reflection_classes[$name])) {
            return $this->reflection_classes[$name];
        }

        return $this->reflection_class[$name] = new \ReflectionClass($name);
    }

    /**
     * Returns and instance of a Haystack index.
     *
     * @param string $class
     * @return object
     */
    public function getIndexInstance($class)
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        return $this->instances[$class] = $this->getIndexReflectionClass($class)->newInstance();
    }

    // NOT NEEDED?

    public function getFieldValue($index_name, $field_name, $model)
    {

        $reflection = $this->getIndexReflectionClass($index_name);

        if (!$reflection->hasProperty($field_name))
            throw new \Exception('Property "' . $field_name . '" does not exist on index' . $index_name);

        $method = 'prepare_' . $field_name;

        if ($reflection->hasMethod($method))
            return $model->$method();

        $properties = $reflection->getDefaultProperties();
        $conf = $properties[$field_name];

        if (isset($conf['model_attr']) && isset($model->$conf['model_attr']))
            return $model->$conf['model_attr'];

        throw new \Exception("There was no 'model_attr' for field '$field_name' on index '$index_name'");
    }

    public function getFieldAndValues($index_name, $doc)
    {

        $data = array();
        $index = $this->getIndexInstance($index_name);
        $reflection = $this->getIndexReflectionClass($index_name);
        $properties = $reflection->getDefaultProperties();
        //$base_model = &$doc[$index->getModelName()];

        foreach ($properties as $prop => $conf) {

            if ($prop == '_settings')
                continue;

            $method = 'prepare_' . $prop;

            if ($reflection->hasMethod($method)) {
                $data[$prop] = $index->$method($doc);

            } else if (isset($conf['model_attr'])) {

                if (!isset($doc->$conf['model_attr'])) {
                    $data[$prop] = null;

                } else {
                    $data[$prop] = $doc->$conf['model_attr'];
                }

            } else {
                throw new \Exception("There was no 'model_attr' for field '$prop' on index '$index_name' nor a prepare_ function.");
            }
        }
        return $data;
    }
}
