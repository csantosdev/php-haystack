<?php
namespace Haystack\Engines\Elasticsearch\Query\Conditions;
/**
 * Returns the type of condition based on the filter conditions string.
 *
 * @author Chris Santos
 */
class ConditionFactory {
	
    /**
     * Returns an instances of Condition.
     *
     * @param string $field
     * @param mixed $value
     * @param string $operator
     *
     * @return string
     */
    public static function get($field, $value, $operator) {

        if(is_array($value))
            return new TermsCondition($field, $value, $operator);

        switch($operator) {

            case '__lt':
                $condition =  new LessThanCondition($field, $value, $operator);
                break;

            case '__lte':
                $condition =  new LessThanOrEqualCondition($field, $value, $operator);
                break;

            case '__gt':
                $condition =  new GreaterThanCondition($field, $value, $operator);
                break;

            case '__gte':
                $condition =  new GreaterThanOrEqualCondition($field, $value, $operator);
                break;

            case '':
            case '__contains':
            case '__icontains':
            case '__exact':
            case '__iexact':
                $condition =  new StringCondition($field, $value, $operator);
            break;

            default:
                throw new \Exception('Could not find proper Condition class to use for provided condition.');
        }

        return $condition;
    }
}