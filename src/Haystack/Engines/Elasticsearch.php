<?php
namespace Haystack\Engines;

use Haystack\Engines\Elasticsearch\Query\QuerySet;

class ElasticSearch extends \Haystack\Engines\Engine
{
	private $client;
	
	public function __construct($conf) {

		parent::__construct($conf);
		$params = array('hosts' => array($conf['host']));
		$this->client = new \Elasticsearch\Client($params);
	}

    /**
     * {@inheritdoc}
     */
    public function createIndex($class, array $options = null)
    {
        $index = $this->getIndexInstance($class);

        if(!method_exists($index, 'getTypeName')) {
            throw new \Exception('Haystack index ' . $class . ' requires a "getTypeName" method when using an Elasticsearch engine');
        }

        $type = $index->getTypeName();

        if(!is_string($type)) {
            throw new \Exception('Haystack index class method "getTypeName" is required to return a string.');
        }

        $params = array(
            'index' => $index->getIndexName(),
            'body' => array(
                'mappings' => array(
                    $type => array(
                        'properties' => $this->createIndexSchema($index)
                    )
                )
            )
        );

        $params['body']['settings'] = $index->getDefaultConfiguration();

        var_dump($params);
        $this->client->indices()->create($params);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($class, array $options = null)
    {
        $index = $this->getIndexInstance($class);

        $this->client->indices()->delete(array(
            'index' => $index->getIndexName()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function updateIndex($name, array $options = null)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function indexExists($class)
    {
        $index = $this->getIndexInstance($class);
        $params = array('index' => $index->getIndexName());

        return $this->client->indices()->exists($params);
    }

    /**
     * {@inheritdoc}
     */
    public function indexDocument($class, $document)
    {
        $index = $this->getIndexInstance($class);

        $params = array(
            'index' => $index->getIndexName(),
            'id' => $document->id,
            'type' => $index->getTypeName(),
            'body' => array(
                'doc' => $this->getFieldAndValues($class, $document)
            )
        );

        return $this->client->create($params);
    }

    /**
     * {@inheritdoc}
     */
    public function updateDocument($index, $document)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function upsertDocument($class, $document)
    {
        $index = $this->getIndexInstance($class);

        $params = array(
            'index' => $index->getIndexName(),
            'id' => $document->id,
            'type' => $index->getTypeName(),
            'body' => array(
                'doc' => $this->getFieldAndValues($class, $document),
                'doc_as_upsert' => true
            )
        );

        return $this->client->update($params);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDocument($index, $id)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function bulkIndexDocuments($index, array $documents)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function bulkUpdateDocuments($index, array $documents)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function bulkDeleteDocuments($index, array $document_ids)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultIndexConfiguration()
    {
        return array(
            'number_of_shards' => 1,
            'number_of_replicas' => 0,

            'analysis' => array(

                'filter' => array(
                    'haystack_ngram_filter' => array(
                        'type' => 'edgeNGram',
                        'min_gram' => 1,
                        'max_gram' => 5
                    )
                ),

                'analyzer' => array(
                    'haystack_iexact_analyzer' => array(
                        'tokenizer' => 'keyword',
                        'filter' => 'lowercase'
                    ),
                    'haystack_search_analyzer' => array(
                        'tokenizer' => 'whitespace'
                    ),
                    'haystack_isearch_analyzer' => array(
                        'tokenizer' => 'whitespace',
                        'filter' => 'lowercase'
                    )
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldClass($type)
    {
        $class = sprintf('Haystack\Engines\Elasticsearch\Fields\%s', $type);

        if(!class_exists($class)) {
            throw new \Exception('Field type ' . $class . ' could not be found.');
        }

        return $class;
    }

    /**
     * {@inheritdoc}
     */
	public function getClient()
    {
		return $this->client;
	}

    /**
	public function createIndex($classname) {
		$index = $this->getIndexInstance($classname);
		$params = array('index' => $index->getIndexName());

		$params = array(
			'index' => $index->getIndexName(),
			'type' => '',
			'body' => array(
				'mappings' => array(
					$index->getTypeName() => array(
						'properties' => $this->createMapping($classname)
					)
				)
			)
		);

		if(isset($index->_settings))
			$params['body']['settings'] = $index->_settings;

		$this->client->create($params);
	}
     * */
	
	/*
	 public function updateIndex($name) {
		$this->createIndex($name);
	}
	*/
	
	/*

	 public function deleteIndex($classname) {
		$index = $this->getIndexInstance($classname);
		$params = array(
			'index' => $index->getIndexName(),
		);
		$this->client->indices()->delete($params);
	}
	*/

	public function index($classname, $doc) {
		$index = $this->getIndexInstance($classname);
		$params = array(
			'index' => $index->getIndexName(),
			'id' => $doc->id,
			'type' => $index->getTypeName(),
			'body' => array(
				'doc' => $this->getFieldAndValues($classname, $doc),
				'doc_as_upsert' => true
			)
		);
		return $this->client->update($params);
	}

	public function createAlias($name, $index_name) {
		$params = array(
			'index' => '',
			'name' => strtolower($name)
		);
		$this->client->indices()->putAlias($params);
	}

	public function updateAlias($name, $index_name) {
		$this->createAlias($name, $index_name);
	}
	
	public function searchIndex() {
		
	}
	
	public function createIndexBulk() {
		// Use ElasticSearch's built-in bulk REST API
	}
	
	public function bulkIndex($classname, $docs) {
		$index = $this->getIndexInstance($classname);
		$params = array(
			'index' => $index->getIndexName(),
			'type' => $index->getTypeName(),
			'body' => array()
		);
		
		foreach($docs as $doc) {
			$params['body'][] = array(
				'update' => $index->getIndexName(),
				//'_id' => $doc[$index->getModelName()]['id']
				'_id' => $doc->id
			);
			$params['body'][] = array(
				'doc' => $this->getFieldAndValues($classname, $doc),
				'doc_as_upsert' => true
			);
			unset($doc);
		}
		
		return $this->client->bulk($params);
	}
	
	public function updateIndexBulk() {
		
	}
	
	public function deleteBulk() {
		
	}
	
	public function find($model_name, $conditions) {
		$index_name = $this->getIndexNameByModel($model_name);
		$index = $this->getIndexInstance($index_name);
		$params = array(
			'index' => $index->getIndexName(),
			'type' => $index->getTypeName(),
			'body' => array(
				'query' => array(
					'term' => $conditions
				)
			)
				
		);
		return $this->client->search($params);
	}
	
	public function get($index_name, $conditions) {
		$qs = new QuerySet($index_name);
		return $qs->get($conditions);
	}
		
	public function filter($index_name, $conditions) {
		$qs = new QuerySet($index_name);
		$qs->filter($conditions);
		return $qs;
	}
	
	private function createMapping($index_name) {
		
		$index = $this->getIndexInstance($index_name);
		$reflection = $this->getIndexReflectionClass($index_name);
		$properties = $reflection->getDefaultProperties();
		$mapping = array();
		
		$index_prop_ignore = array('_settings');
		$field_conf_ignore = array('model_attr');
		
		foreach($properties as $prop => $conf) {
			
			if(in_array($prop, $index_prop_ignore))
				continue;
			
			if(isset($conf['haystack_config'])) {
				$mapping[$prop] = $index->getFieldMapping($conf['haystack_config'], $prop);
				foreach($conf as $key => $val) {
					if(in_array($key, $field_conf_ignore))
						continue;
					$mapping[$prop][$key]  =$val;
				}
			}
			else
				$mapping[$prop] = $conf;
			
			if(!isset($mapping[$prop]['type']))
				throw new \Exception('No "type" was set an on Haystack index "' . $index_name . '" for field "' . $prop .'". Please check your index configurations.');
		}
		
		return $mapping;
	}

    /**
     * Returns an array to feed into the client library for insert and update document requests.
     *
     * @param string $class Index class name.
     * @param mixed $document Object of data to be used in building
     * @return array
     */
    private function buildRequest($class, $document)
    {
        $index = $this->getIndexInstance($class);

        if(!method_exists($index, 'getTypeName')) {
            throw new \Exception('Haystack index ' . $class . ' requires a "getTypeName" method when using an Elasticsearch engine');
        }

        $type = $index->getTypeName();

        if(!is_string($type)) {
            throw new \Exception('Haystack index class method "getTypeName" is required to return a string.');
        }

        $request = array(
            'index' => $index->getIndexName(),
            'id' => $document->id,
            'type' => $type,
            'body' => array(
                'doc' => $this->getFieldAndValues($class, $document)
            )
        );

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function createIndexSchema(\Haystack\Index $index)
    {
        $fields = get_object_vars($index);
        $schema = $this->createFieldSchema($fields);
        return $schema;
    }

    /**
     * Recursively creates the schema/mapping for each Field provided. The NestedField type is what will utilize recursion.
     *
     * @param array $fields
     */
    private function createFieldSchema($fields)
    {
        $schema = array();

        foreach($fields as $field_name => $field_value) {
            $field_class = $this->getFieldClass($field_value[0]);

            if($field_class === 'Haystack\Engines\Elasticsearch\Fields\NestedField') {
                $schema[$field_name] = array(
                    'type' => 'nested',
                    'properties' => $this->createFieldSchema($field_value['fields'])
                );

            } else {
                $field = new $field_class($field_name, $field_value, $this);
                $schema[$field_name] = $field->toSchema();
            }
        }

        return $schema;
    }
}