<?php
namespace Haystack\Query;
/**
 * Allows for chaining filter methods to build search query and an iteration interface for accessing
 * the returned data from the search engine.
 *
 * @author Chris Santos <csantosdev@gmail.com>
 */
interface QuerySet extends \ArrayAccess, \Iterator, \Countable
{
    /**
     * Query for a single document in the search engine.
     *
     * @param array $conditions
     * @throws \Exception
     */
    public function get($conditions);

    /**
     * Query and return all documents on this index.
     *
     * @return \Haystack\Engines\Elasticsearch\Query\QuerySet
     */
    public function all();

    /**
     * Add filter conditions to the Query object.
     *
     * @param array $conditions
     * @return \Haystack\Query\QuerySet
     */
    public function filter($conditions);

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset);

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset);

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value);

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset);

    /**
     * {@inheritdoc}
     */
    public function rewind();

    /**
     * {@inheritdoc}
     */
    public function current();

    /**
     * {@inheritdoc}
     */
    public function key();

    /**
     * {@inheritdoc}
     */
    public function next();

    /**
     * {@inheritdoc}
     */
    public function valid();

    /**
     * Sets the result offset count for the query.
     *
     * @return \Haystack\Query\QuerySet
     */
    public function offset($num);

    /**
     * Sets the limit of the number of results returned by the query.
     *
     * @param int $num
     * @return \Haystack\Query\QuerySet
     */
    public function limit($num);

    /**
     * Returns the number of objects returned by the query.
     *
     * @return int
     */
    public function count();

    /**
     * Returns the total about of documents available for this query.
     *
     * @return int
     */
    public function getTotalResultCount();

    /**
     * Returns the data from the query
     * @return array
     */
    public function toArray();

    /**
     * Returns the query used.
     *
     * @return string
     */
    public function getQuery();

    /**
     * Sets the fields to be used when ordering the results from the query.
     *
     * @return \Haystack\Query\QuerySet
     */
    public function order();
}
