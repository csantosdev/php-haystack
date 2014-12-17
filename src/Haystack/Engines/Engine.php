<?php
namespace Haystack\Engines;
/**
 * The basics to the search engine Engine class. Provides functionality to interact with managing
 * indexes and documents.
 * TODO: This class needs to be broken up into 2 classes. One for querying calls and another for managing the indexes.
 *
 * @package Haystack\Engines
 */
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
        //$this->index_classes = $conf['indexes'];
    }

    /**
     * Creates a new index within the search engine.
     *
     * @param string $class Classname of the Haystack index.
     * @param array $options Engine specific configuration options.
     * @return bool
     * @throws \Exception
     */
    abstract public function createIndex($class, array $options = null);

    /**
     * Deletes an existing index within the search engine.
     *
     * @param string $class Classname of the Haystack index.
     * @param array $options Engine specific configuration options.
     * @return bool
     * @throws \Exception
     */
    abstract public function deleteIndex($class, array $options = null);

    /**
     * Updates an existing index.
     *
     * @param string $class Classname of the Haystack index.
     * @param array $options Engine specific configuration options.
     * @return bool
     * @throws \Exception
     */
    abstract public function updateIndex($class, array $options = null);

    /**
     * Returns TRUE if the index exists in the search engine.
     *
     * @param string $class Classname of the Haystack index.
     * @return bool
     * @throw \Exception
     */
    abstract public function indexExists($class);

    /**
     * Index a document into an existing index.
     *
     * @params string $index Index name.
     * @params array $document Array of data to index.
     * @return bool
     * @throws \Exception
     */
    abstract public function indexDocument($index, $document);

    /**
     * Update a document within an index.
     *
     * @params string $index Index name.
     * @params array $document Array of data to update the document.
     * @return bool
     * @throws \Exception
     */
    abstract public function updateDocument($index, $document);

    /**
     * Delete a document within an index.
     *
     * @params string $index Index name.
     * @params string $id ID of the document to delete.
     * @return bool
     * @throws \Exception
     */
    abstract public function deleteDocument($index, $id);

    /**
     * Bulk index many documents within an index.
     *
     * @param string $index Index name.
     * @param array $documents Array of data to update the index with.
     * @return bool
     * @throws \Exception
     */
    abstract public function bulkIndexDocuments($index, array $documents);

    /**
     * Bulk update documents within an index.
     *
     * @params string $index Index name.
     * @params array $documents Array of array data to update the index with.
     * @return bool
     * @throws \Exception
     */
    abstract public function bulkUpdateDocuments($index, array $documents);

    /**
     * Bulk delete documents within an index.
     *
     * @params string $index Index name.
     * @params array $document_ids Array of IDs to delete from the index.
     * @return bool
     * @throws \Exception
     */
    abstract public function bulkDeleteDocuments($index, array $document_ids);

    public function getIndexNameByModel($name)
    {
        if (!isset($this->index_classes[$name])) {
            throw new \Exception('There is no index for model name: ' . $name);
        }

        return $this->index_map[$name];
    }

    /**
     * Returns the name of the literal index on the search engine.
     *
     * If one is not provided on the Haystack index, then the classname
     * will be used.
     *
     * @param string $class Haystack index class name.
     * @return string
     */
    public function getIndexName($class)
    {
        $index = $this->getIndexInstance($class);

        if(method_exists($index, 'getIndexName')) {
            return $index->getIndexName();
        }

        return str_replace('\\', '', $class);
    }

    public function getIndexReflectionClass($name)
    {

        if (isset($this->reflection_classes[$name])) {
            return $this->reflection_classes[$name];
        }

        return $this->reflection_class[$name] = new \ReflectionClass($name);
    }

    /**
     * Returns and instance of a Haystack index object.
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
