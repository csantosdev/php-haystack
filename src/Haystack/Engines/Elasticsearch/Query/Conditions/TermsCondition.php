<?php
namespace Haystack\Engines\Elasticsearch\Query\Conditions;
/**
 * Represents the Term ElasticSearch query.
 */
class TermsCondition implements Condition {

    private $field;
    private $value;
    private $operator;

    private $lowercase = false;

    public function __construct($field, $value, $operator) {

        $this->field = $field;
        $this->value = $value;
        $this->operator = $operator;
    }

    /**
     * Returns a representation of a filter in ElasticSearch.
     */
    public function toFilter() {

        $filter['terms'] = array($this->field => $this->value);
        return $filter;
    }

    public function setLowercased() {
        $this->lowercase = true;
    }

    private function escape($s) {
        return str_replace(' ', '\\ ', $s);
    }
}