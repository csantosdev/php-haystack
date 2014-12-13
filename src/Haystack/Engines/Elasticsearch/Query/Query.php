<?php
namespace Haystack\Engines\Elasticsearch\Query;
/**
 * Responsible for storing user conditions from the ORM and building them into
 * an ElasticSearch query.
 * 
 * @author Chris Santos
 *
 */
use Haystack\Engines\Elasticsearch\Query\Conditions\NestedCondition;
use Haystack\Engines\Elasticsearch\Query\Conditions\ConditionFactory;

class Query {
	
	const _AND_ = 'AND';
	const _OR_ = 'OR'; 
	
	/**
	 * Actual ElasticSearch query;
	 * 
	 * @var array
	 */
	private $query;
	
	/**
	 * Used to reference the current query when building the ElasticSearch query recursively.
	 * 
	 * @var array
	 */
	private $current_query;
	
	/**
	 * Used to know which type the $current_query reference is pointing to.
	 * 
	 * @var string
	 */
	private $current_query_type;
	
	/**
	 * Array of 'Condition' objects. Represents a ElasticSearch filter.
	 * 
	 * @var array
	 */
	private $conditions;
	
	/**
	 * Array of filter conditions the user sent using the Haystack ORM.
	 * ex: Haystack::filter(['name' => 'Chris']);
	 * 
	 * @var array
	 */
	private $filters = array();

    /**
     * List of fields to sort by.
     *
     * @var array
     */
    private $sort;
	
	/**
	 * Class name of the Haystack index.
	 * 
	 * @var string
	 */
	private $index_name;
	
	private $operators = '__isnull|__gte|__lte|__gt|__lt|__in|__exact|__iexact|__contains|__icontains|__startswith|__istartswith|__endswith|__iendswith';
    private static $_operators = '__isnull|__gte|__lte|__gt|__lt|__in|__exact|__iexact|__contains|__icontains|__startswith|__istartswith|__endswith|__iendswith';
	
	
	/* Haystack::filter(
	 * 	'name' => 'Chris',
	 * 	'OR' => [
	 * 		'meta.value' => 'test'
	 * 	]
	 * ]);
	 * 
	 * Haystack::filter(['name' => 'Chris')
	 * 	->or(['meta.value' => 'test']);
	 * 
	 * Haystack::filter(array(
	 * 		array('name' => 'Chris),
	 * 		'OR',
	 * 		array('meta.value' => 'test')
	 * ));
	 * 
	 * Haystack::filter(array(
	 * 		'name' => 'Chris',
	 * 		'meta.value' => 'test,
	 * 		'OR',
	 * 		'name' => 'Jon'
	 * 		'OR'
	 *		) 
	 * ));
	 * 
	 * // WHERE (name=Chris) AND (meta.value=test)
	 * 
	 * "filter" : {
                "or": {
                    "filters": [
                        {
                            "and": {
                               "filters": [
                                  {}
                               ]
                            }, 
                            "fquery": {
                               "query": {
                                    "query_string": {
                                       "query": "title:*Scarf*",
                                       "lowercase_expanded_terms": false
                                    }
                               }
                            }   
                        },
                        {
                            "nested": {
                               "path": "meta",
                               "query": {
                                   "query_string": {
                                      "query": "meta.id:1"
                                   }
                               }
                            }
	 */

    public static function _findOperator(&$query) {

        $matches_found = preg_match('/' . self::$_operators . '/', $query, $matches);

        if($matches_found === 0)
            return null;
        else if($matches_found > 1)
            throw new \Exception('Multiple operators found in query: ' . $query);

        $query = str_replace($matches[0], '', $query);
        return $matches[0];
    }

    /**
	 * Add a condition segment of the entire query.
	 * 
	 * @param array $condition
	 */
	public function addFilter($filter) {
		$this->filters[] =  $filter;
	}

    /**
     * Set a list of fields to sort by.
     *
     * @param arary $fields
     */
    public function setSort($fields) {
        $this->sort = $fields;
    }

    /**
     * Returns the sort query.
     *
     * @return array
     */
    public function getSort() {
        return $this->sort;
    }

	/**
	 * Parses the filter conditions and creates the ElasticSearch query;
	 */
	public function parse() {
		
		if(empty($this->filters))
			return array();
		
		$conditions = $this->build($this->filters);
		$query = $this->buildElasticSearchQuery($conditions);
		return $query;
    }
	
	/**
	 * Parse the conditions into ElasticSearch conditions.
	 */
	private function build($filters) {
		
		$conditions = array();

        foreach($filters as $filter_conditions) {

            $nested_filters = array();

            foreach($filter_conditions as $field => $value) {

                if(is_int($field)) {

                    if(is_array($value)) {
                        $conditions[] = $this->build($value);
                        continue;

                    } else if($value === Query::_OR_) {
                        $conditions[] = 'OR';
                        continue;
                    }
                }

                $operator = $this->findOperator($field);
                $filter = ConditionFactory::get($field, $value, $operator);

                // Handle Nested Filters
                if(strpos($field, '.') !== false) {

                    $fields = explode('.', $field);
                    $key = $fields[0];

                    if(!isset($nested_filters[$key])) {
                        $nested_filter = new NestedCondition($field, $value, null);
                        $nested_filters[$key] = $nested_filter;
                        $conditions[] = &$nested_filters[$key];
                    }

                    $nested_filters[$key]->addInnerFilter($filter);
                    continue;
                }

                $conditions[] = $filter;

                /*
                switch($operator) {

                    case '__isnull':
                        if($value === false)
                            $segment = sprintf('_missing_:%s', $condition);
                        else
                            $segment = sprintf('_exists_:%s', $condition);
                        break;

                    case '__in':
                        exit("NOT IMPLEMENTED");
                        $segment = array();
                        foreach($value as $v)
                            $segment = array('match' => array($piece => $v));
                        break;

                    case '__exact':
                        $segment = sprintf('%s:"%s"', $condtion, $value);
                        break;

                    case '__iexact':
                        $segment = sprintf('%s.iexact:"%s"', $condition, $value);
                        break;

                    case '__contains':
                        $segment = sprintf('%s:*%s*', $condition, $this->escape($value));
                        $lowercase = false;
                        break;

                    case '__icontains':
                        $segment = sprintf('%s.iexact:*%s*', $condition, $this->escape($value));
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

                    default:
                        if(is_array($value)) {
                            foreach($value as $v)
                                $segment = sprintf('%s:"%s"', $condition, $value);
                        } else {
                            if(is_string($value))
                                $segment = sprintf('%s:"%s"', $condition, $value);
                            else
                                $segment = sprintf('%s:%s', $condition, $value);
                        }

                        break;
                }
                */

            }
        }

        if($this->sort)
            $this->sort = SortQueryBuilder::build($this->sort);

		return $conditions;
	}
	
	/**
	 * Turns the $conditions array into an ElasticSearch query.
	 */
	private function buildElasticSearchQuery($conditions) {

		if(empty($conditions))
		    return array();
		$query['bool']['must'] = array();
		$current_query =& $query['bool']['must'];
		$current_query_type = null;
		$or = false;
		
		foreach($conditions as $condition) {
		
			if(!is_a($condition, 'Haystack\Engines\Elasticsearch\Query\Condition') && $condition === 'OR') {
				$or = true;
					
			} else {
		
				if($or) {
					/*
					 * Handling OR: set the main query to an OR if it's currently not and put
					 * any previous filters into an AND.
					 */
					$or = false;
					
					if(isset($query['bool'])) {
						$new_query['or']['filters'] = $query['bool']['must'];
						$query = $new_query;
						$current_query =& $query['or']['filters'];
						$current_query_type = Query::_OR_;
					
					} else if(isset($query['and'])) {
						$new_and = array();
						$new_and['and']['filters'] = $query['and']['filters'];
						$new_query = array();
						$new_query['or']['filters'] = array($new_and);
						$query = $new_query;
						$current_query =& $query['or']['filters'];
						$current_query_type = Query::_OR_;
					}
					
					if(is_array($condition))
						$query['or']['filters'][] = $this->buildElasticSearchQuery($condition);
					else
						$current_query[] = $condition->toFilter();
						
				} else {
					
					/*
					 * Handling AND: set the current query to an AND if it's currently not and put
					 * the previous filter into it.
					 */
					if(isset($query['bool']) && count($current_query) > 0) {
						$new_query['and']['filters'] = $query['bool']['must'];
						$query = $new_query;
						$current_query =& $query['and']['filters'];
						$current_query_type = Query::_AND_;
					
					} else if(isset($query['or']) && $current_query_type == Query::_OR_) {
						$count = count($query['or']['filters']) - 1;
						$arr['and']['filters'] = array_splice($current_query, $count);
						$current_query[] =& $arr;
						$current_query =& $arr['and']['filters'];
						$current_query_type = Query::_And_;
					}
					
					if(is_array($condition))
						$query['and']['filters'][] = $this->buildElasticSearchQuery($condition);
					else
						$current_query[] = $condition->toFilter();
				}
			}
		}
		
		return $query;
	}
	
	private function findOperator(&$query) {
	
		$matches_found = preg_match('/' . $this->operators . '/', $query, $matches);
	
		if($matches_found === 0)
			return null;
		else if($matches_found > 1)
			throw new \Exception('Multiple operators found in query: ' . $query);
	
		$query = str_replace($matches[0], '', $query);
		return $matches[0];
	}
	
	private function escape($s) {
		return str_replace(' ', '\\ ', $s);
	}
}
