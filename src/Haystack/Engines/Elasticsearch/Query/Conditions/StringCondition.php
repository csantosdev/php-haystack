<?php
namespace Haystack\Engines\Elasticsearch\Query\Conditions;
/**
 * Represents one of many conditions of an ElasticSearch query.
 */
class StringCondition implements Condition {

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
                $query = sprintf('%s:"%s"', $this->field, $this->value);
                break;

            case '__exact':
                $query = sprintf('%s:"%s"', $this->field, $this->value);
                break;

            case '__iexact':
                $query = sprintf('%s.iexact:"%s"', $this->field, $this->value);
                break;

            case '__contains':
                $query = sprintf('%s:*%s*', $this->field, $this->escape($this->value));
                $this->lowercase = false;
                break;

            case '__icontains':
                $query = sprintf('%s.iexact:*%s*', $this->field, $this->escape($this->value));
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

		$filter['fquery']['query']['query_string'] = array(
			'query' => $query,
			'lowercase_expanded_terms' => $this->lowercase
		);
		return $filter;
	}

	public function setLowercased() {
		$this->lowercase = true;
	}

    private function escape($s) {
        return str_replace(' ', '\\ ', $s);
    }
}