<?php
namespace Haystack\Engines;
/**
 * The basics to the search engine Engine class. Provides functionality to interact with managing
 * indexes and documents.
 *
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
     * @params string $class Index class name.
     * @params mixed $document Object to index.
     * @return bool
     * @throws \Exception
     */
    abstract public function indexDocument($class, $document);

    /**
     * Update a document within an index.
     *
     * @params string $class Index class name.
     * @params mixed $document Object to update the document.
     * @return bool
     * @throws \Exception
     */
    abstract public function updateDocument($class, $document);

    /**
     * Update a document within an index, if it does not exist create one.
     *
     * @params string $class Index class name.
     * @params mixed $document Object to update/insert the document.
     * @return bool
     * @throws \Exception
     */
    abstract public function upsertDocument($class, $document);

    /**
     * Delete a document within an index.
     *
     * @params string $class Index class name.
     * @params string $id ID of the document to delete.
     * @return bool
     * @throws \Exception
     */
    abstract public function deleteDocument($class, $id);

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

    /**
     * Returns an array of the default settings for the index being created.
     *
     * @return array
     */
    abstract public function getDefaultIndexConfiguration();

    /**
     * Returns the string of a field type class defined in the Haystack index.
     *
     * @param $type Haystack field type name.
     * @return string
     * @throws \Exception
     */
    abstract public function getFieldClass($type);

    /**
     * Returns the instance of the client library used to access the search engine.
     *
     * @return mixed
     */
    abstract public function getClient();

    /**
     * Returns the schema/mapping for the provided Index instance.
     *
     * @return mixed
     */
    abstract public function createIndexSchema(\Haystack\Index $index);

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
     * @return \Haystack\Index
     */
    public function getIndexInstance($class)
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        return $this->instances[$class] = new $class($this);
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

    /**
     * TODO: RENAME THIS METHOD TO SOMETHING MORE ACCURATE
     *
     * Returns an array of data that will be stored into a document.
     *
     * @param string $index_name
     * @param array $doc
     * @return array
     * @throws \Exception
     */
    public function getFieldAndValues($index_name, $doc)
    {
        $data = array();
        $index = $this->getIndexInstance($index_name);
        //$reflection = $this->getIndexReflectionClass($index_name);
        //$properties = $reflection->getDefaultProperties();

        $properties = get_object_vars($index);

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
