<?php
namespace Haystack\Engines\Elasticsearch;
/**
 * Elasticsearch engine class.
 * Implementation of a Haystack engine using the official Elasticsearch
 * client library.
 *
 * @author Chris Santos <csantosdev@gmail.com>
 */
use Haystack\Engines\Elasticsearch\Query\QuerySet;

class ElasticSearch extends \Haystack\Engines\Engine
{
    private $client;

    public function __construct($conf)
    {
        parent::__construct($conf);
        $params = array('hosts' => array($conf['host']));
        $this->client = new \Elasticsearch\Client($params);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function updateIndex($name)
    {
        $this->createIndex($name);
    }

    public function createIndex($classname)
    {
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

        if (isset($index->_settings)) {
            $params['body']['settings'] = $index->_settings;
        }

        $this->client->create($params);
    }

    private function createMapping($index_name)
    {
        $index = $this->getIndexInstance($index_name);
        $reflection = $this->getIndexReflectionClass($index_name);
        $properties = $reflection->getDefaultProperties();
        $mapping = array();

        $index_prop_ignore = array('_settings');
        $field_conf_ignore = array('model_attr');

        foreach ($properties as $prop => $conf) {

            if (in_array($prop, $index_prop_ignore)) {
                continue;
            }

            if (isset($conf['haystack_config'])) {
                $mapping[$prop] = $index->getFieldMapping($conf['haystack_config'], $prop);
                foreach ($conf as $key => $val) {
                    if (in_array($key, $field_conf_ignore))
                        continue;
                    $mapping[$prop][$key] = $val;
                }
            } else
                $mapping[$prop] = $conf;

            if (!isset($mapping[$prop]['type'])) {
                throw new \Exception('No "type" was set an on Haystack index "' . $index_name . '" for field "' . $prop . '". Please check your index configurations.');
            }
        }

        return $mapping;
    }

    public function deleteIndex($classname)
    {
        $index = $this->getIndexInstance($classname);
        $params = array(
            'index' => $index->getIndexName(),
        );
        $this->client->indices()->delete($params);
    }

    public function index($classname, $doc)
    {
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

    public function updateAlias($name, $index_name)
    {
        $this->createAlias($name, $index_name);
    }

    public function createAlias($name, $index_name)
    {
        $params = array(
            'index' => '',
            'name' => strtolower($name)
        );
        $this->client->indices()->putAlias($params);
    }

    public function searchIndex()
    {

    }

    public function createIndexBulk()
    {
        // Use ElasticSearch's built-in bulk REST API
    }

    public function bulkIndex($classname, $docs)
    {
        $index = $this->getIndexInstance($classname);
        $params = array(
            'index' => $index->getIndexName(),
            'type' => $index->getTypeName(),
            'body' => array()
        );

        foreach ($docs as $doc) {
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

    public function updateIndexBulk()
    {

    }

    public function deleteBulk()
    {

    }

    public function find($model_name, $conditions)
    {
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

    public function get($index_name, $conditions)
    {
        $qs = new QuerySet($index_name);
        return $qs->get($conditions);
    }

    public function filter($index_name, $conditions)
    {
        $qs = new QuerySet($index_name);
        $qs->filter($index_name, $conditions);
        return $qs;
    }

    private function throwError(\Exception $e)
    {
        $m = json_decode($e->getMessage());
        throw new \Exception('Status: ' . $m->status . '. ' . $m->error);
    }
}