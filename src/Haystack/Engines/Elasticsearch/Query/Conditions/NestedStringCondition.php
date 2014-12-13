<?php
namespace Haystack\Engines\Elasticsearch\Query;
/**
 * Represents one of many conditions of an ElasticSearch query.
 */
class NestedStringCondition implements Condition {

    private $field;
    private $value;
    private $operator;

    private $combined_filters = array();
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
        }

        $fields = explode('.', $this->field);

		$filter['nested'] = array('path' => $fields[0]);
		$filter['nested']['filter']['bool']['must'][] = array(
            'term' => array($this->field => $this->value)
        );

        if(!empty($this->combined_filters)) {
            foreach($this->combined_filters as $f) {
                $filter['nested']['filter']['bool']['must'][] = $f['nested']['filter']['bool']['must'][0];
            }
        }

		return $filter;
	}

 	public function setLowercased() {
		$this->lowercase = false;
	}

    /**
     * Takes a filter condition from another object and
     * @param NestedStringCondition $condition
     */
    public function combineCondition(NestedStringCondition $condition) {

        $this->combined_filters[] = $condition->toFilter();
    }
}