<?php
namespace Haystack\Engines\Elasticsearch\Query\Conditions;
/**
 * Represents one of many conditions of an ElasticSearch query.
 *
 * @author Chris Santos
 */
interface Condition {

    /**
     * Build a representation of a condition.
     *
     * @param $field
     * @param $value
     * @param $operator
     */
    public function __construct($field, $value, $operator);

	/**
	 * Returns a representation of a filter in ElasticSearch. 
	 */
	public function toFilter();
	
	public function setLowercased();
}