<?php
namespace Haystack\Engines\Elasticsearch\Query;
/**
 * Responsible for returning a sort query based on the type of sort input provided.
 *
 * @author Chris Santos
 */
class SortQueryBuilder {

    /**
     * Returns an ElasticSearch sort query.
     *
     * Can return a normal sort or a nested filter sort.
     *
     * @param string|array $field
     */
    public static function build($fields) {

        $sorts = array();
        foreach($fields as $field) {

            if(is_array($field)) {

                foreach($field as $nested_field => $nested_filter) {
                    $direction = self::getSortDirection($nested_field);
                    $sort = array($nested_field => array(
                        'nested_filter' => array('term' => $nested_filter),
                        'order' => $direction
                    ));
                    $sorts[] = $sort;
                }

            } else {
                $direction = self::getSortDirection($field);
                $sorts[] = array($field => $direction);
            }
        }

        return array('sort' => $sorts);
    }

    /**
     * Removes the '-' in the field name and returns either 'desc' or 'asc'.
     *
     * @param string $field
     */
    private static function getSortDirection(&$field) {

        if(strpos($field, '-') === 0) {
            $field = substr($field, 1, strlen($field)-1);
            return 'desc';
        }

        return 'asc';
    }
}