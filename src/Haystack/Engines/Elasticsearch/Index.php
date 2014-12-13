<?php
namespace Haystack\Engines\Elasticsearch;
/* This abstract class serves as a base for how to configure and handle your indexes in ElasticSearch.
 * By default each model will have it's own index with a single shard.
 * 
 * If you need to have multiple types within an index you can override the method in the that index's
 * class configuration to have the same index for all the types you need.
 */
abstract class Index
{
	public $id = ['type' => 'integer', 'model_attr' => 'id'];
	
	public function getIndexName()
    {
		return get_class($this);
	}
	
	public function getTypeName()
    {
		return 'default';
	}
	
	public $_settings = array(
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
	
	/**
	 * Called by the Haystack Engine when mapping fields during Index creation.
	 * 
	 * Note: added backwards compatibility for ElasticSearch v0.90.
	 * 
	 * @param string $field
	 */
	public function getFieldMapping($name, $field=null) {
		
		$mapping = array();
		
		switch($name) {
			
			case 'default':
				
				$mapping = array(
					'type' => 'string',
					'index' => 'not_analyzed',
						
					'fields' => array(
						'iexact' => array(
							'type' => 'string',
							'index' => 'analyzed',
							'index_analyzer' => 'haystack_iexact_analyzer',
							'search_analyzer' => 'haystack_iexact_analyzer'
						),
						'search' => array(
							'type' => 'string',
							'index' => 'analyzed',
							'index_analyzer' => 'haystack_search_analyzer',
							'search_analyzer' => 'haystack_search_analyzer'
						),
						'isearch' => array(
							'type' => 'string',
							'index' => 'analyzed',
							'index_analyzer' => 'haystack_isearch_analyzer',
							'search_analyzer' => 'haystack_isearch_analyzer'
						)
					)
				);
				
				return $mapping;
				
			/*
			 * This is needs to be used if you are running ElasticSearch version 0.90 or lower.
			 */
			case 'v0.90':
			
				$mapping = array(
					'type' => 'string',
					'index' => 'not_analyzed',
				
					'fields' => array(
						'iexact' => array(
							'type' => 'string',
							'index' => 'analyzed',
							'index_analyzer' => 'haystack_iexact_analyzer',
							'search_analyzer' => 'haystack_iexact_analyzer'
						),
						'search' => array(
							'type' => 'string',
							'index' => 'analyzed',
							'index_analyzer' => 'haystack_search_analyzer',
							'search_analyzer' => 'haystack_search_analyzer'
						),
						'isearch' => array(
							'type' => 'string',
							'index' => 'analyzed',
							'index_analyzer' => 'haystack_isearch_analyzer',
							'search_analyzer' => 'haystack_isearch_analyzer'
						)
					)
				);
				
				if($field) {
					$mapping['fields'][$field] = array(
						'type' => 'string',
						'index' => 'not_analyzed'
					);
				}
				
				return $mapping;
				
			default:
				throw new \Exception('There is no Haystack configuration for index field config: ' . $name);
		}
	}
}