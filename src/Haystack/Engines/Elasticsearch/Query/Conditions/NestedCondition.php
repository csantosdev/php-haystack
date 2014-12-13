<?php
namespace Haystack\Engines\Elasticsearch\Query\Conditions;
/**
 * Nested Filter can contain inner filters within in.
 *
 * NOTE: Only supports AND filters.
 *
 * @author Chris Santos
 */
use Haystack\Engines\Elasticsearch\Query\Query;

class NestedCondition implements Condition {

    private $filter;

    private $field;
    private $inner_filter;

    public function __construct($field, $value, $operator) {

        $fields = explode('.', $field);
        $path = array_shift($fields);

        $this->filter['nested'] = array('path' => $path);
        $this->filter['nested']['filter']['and'] = array();
    }

    public function toFilter() {
        return $this->filter;
    }

    public function addInnerFilter(Condition $condition) {
        $this->filter['nested']['filter']['and'][] = $condition->toFilter();
    }

    public function setLowercased() {}
}