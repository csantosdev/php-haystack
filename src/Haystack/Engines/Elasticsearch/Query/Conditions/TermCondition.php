<?php
namespace Haystack\Engines\Elasticsearch\Query\Conditions;
/**
 * Represents the Term ElasticSearch query.
 */
class TermCondition implements Condition {

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

        switch($this->operator) {

            case '':
                $field = sprintf('%s', $this->field);
                $value = sprintf('"%s"', $this->value);
                break;

            case '__exact':
                $field = sprintf('%s:"%s"', $this->field, $this->value);
                break;

            case '__iexact':
                $field = sprintf('%s.iexact:"%s"', $this->field, $this->value);
                break;

            case '__contains':
                $field = sprintf('%s:*%s*', $this->field, $this->escape($this->value));
                $this->lowercase = false;
                break;

            case '__icontains':
                $field = sprintf('%s.iexact', $this->field);
                $value = sprintf('*%s*', $this->escape($this->value));
                $this->lowercase = true;
                break;

            /*
            case '__icontains':
                $condition = sprintf('%s.iexact:*%s*', $condition, $this->escape($value));
                break;

            case '__search':
                $segment = sprintf('%s.search:%s', $condition, $value);
                break;

            case '__isearch':
                $segment = sprintf('%s.isearch:%s', $condition, $value);
                break;

            case '__startswith':
                $segment = sprintf('%s:%s*', $condition, $this->escape($value));
                break;

            case '__istartswith':
                $segment = sprintf('%s.iexact:%s*', $condition, $this->escape($value));
                $lowercase = false;
                break;

            case '__endswith':
                $segment = sprintf('%s:*%s', $condition, $this->escape($value));
                $lowercase = false;
                break;

            case '__iendswith':
                $segment = sprintf('%s.iexact:*%s', $condition, $this->escape($value));
                break;
            */

        }

        $filter['term'] = array($field => $value);
        return $filter;
    }

    public function setLowercased() {
        $this->lowercase = true;
    }

    private function escape($s) {
        return str_replace(' ', '\\ ', $s);
    }
}