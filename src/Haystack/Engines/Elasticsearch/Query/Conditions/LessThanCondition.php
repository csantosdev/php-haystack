<?php
namespace Haystack\Engines\Elasticsearch\Query\Conditions;
/**
 * Represents one of many conditions of an ElasticSearch query.
 */
class LessThanCondition implements Condition {

    private $field;
    private $value;
    private $operator;

    public function __construct($field, $value, $operator) {

        $this->field = $field;
        $this->value = $value;
        $this->operator = $operator;
    }

    public function toFilter() {

        $filter['range'][$this->field]['lt'] = $this->value;
        return $filter;
    }

    public function setLowercased() {
        // Not needed
    }
}