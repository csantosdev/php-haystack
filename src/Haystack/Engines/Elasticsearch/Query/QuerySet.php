<?php
namespace Haystack\Engines\Elasticsearch\Query;
/**
 * Allows you the access your ElasticSearch data the same way you would an Array.
 */
use Haystack\Haystack;

class QuerySet implements \ArrayAccess, \Iterator, \Countable {
	
	/**
	 * Query object: manages creating the ElasticSearch query.
	 * 
	 * @var \Haystack\Engines\Elasticsearch\Query\Query
	 */
	private $query;
	
	/**
	 * Name of the Haystack index this queryset will be using.
	 * 
	 * @var string
	 */
	private $index_name;
	
	/**
	 * ElasticSearch offset of documents returned.
	 * 
	 * @var int
	 */
	private $offset;
	
	/**
	 * ElasticSearch limit of documents returned.
	 * 
	 * @var int
	 */
	private $limit = 10;

	/**
	 * The response array from ElasticSearch.
	 * 
	 * @var array
	 */
	private $response;
	
	/**
	 * The ElasticSearch 'hit' array from the response.
	 * 
	 * @var array
	 */
	private $data;
	
	public function __construct($index_name, $query = null) {
		$this->index_name = $index_name;

        if($query)
		    $this->query = $query;
        else
            $this->query = new Query();
	}
	
	/**
	 * Query a single document in ElasticSearch.
	 * 
	 * @param array $condtions
	 * @throws \Exception
	 */
	public function get($conditions) {
		
		$this->query->addFilter($conditions);
		$count = $this->count();
		if($count == 0)
			throw new \Exception("Could not find " . $this->index_name . ' object in search engine. Query: ' . $this->query);
		else if($count > 1)
			throw new \Exception('More than 1 object was returned for query.');
		return $this->data[0]['_source'];
	}
	
	/**
	 * Query and return all documents on this index.
	 * 
	 * @return \Haystack\Engines\Elasticsearch\Query\QuerySet
	 */
	public function all() {
		return clone $this;
	}
	
	/**
	 * Add filter conditions to the Query object.
	 * 
	 * @param array $criteria
	 * @return \Haystack\Engines\Elasticsearch\Query\QuerySet
	 */
	public function filter($conditions) {
		$this->query->addFilter($conditions);
		return clone $this;
	}
	
	public function offsetExists($offset) {
		$this->fetch();
		return isset($this->data[$offset]);
	}
	
	public function offsetGet($offset) {
		$this->fetch();
		return $this->data[$offset]['_source'];
	}

	public function offsetSet($offset, $value) {
		throw new \Exception('You are not allowed to set an item of a QuerySet once it has been assessed.');
	}

	public function offsetUnset($offset) {
		throw new \Exception('You are not allowed to unset an item of a QuerySet once it has been assessed.');
	}
	
	public function offset($num) {
		$this->offset = $num;
		return clone $this;
	}
	
	public function limit($num) {
		$this->limit = $num;
		return clone $this;
	}
	
	public function rewind() {
		$this->fetch();
		$this->index = 0;
	}
	
	public function current() {
		return $this->data[$this->index]['_source'];
	}
	
	public function key() {
		return $this->index;
	}
	
	public function next() {
		++$this->index;
	}
	
	public function valid() {
		return isset($this->data[$this->index]);
	}
	
	public function count() {
		$this->fetch();
		return count($this->response['hits']['hits']);
	}

    /**
     * Returns the total about of documents available for this query.
     */
    public function getTotal() {
        $this->fetch();
        return $this->response['hits']['total'];
    }
	
	public function toArray() {
		$this->fetch();
		return $this->response['hits']['hits'];
	}

    /**
     * Returns the ElasticSearch query.
     *
     * @return mixed
     */
    public function getQuery() {

        return $this->query->parse();
    }

    /**
     * Stores a list of fields to order by.
     */
    public function order_by() {
        $this->query->setSort(func_get_args());
        return clone $this;
    }
	
	/**
	 * Fetches the data by parsing the conditions, building and querying the ElasticSearch engine.
	 */
	private function fetch() {
		
		if($this->response)
			return;
		
		$index = Haystack::getInstance()->getIndexInstance($this->index_name);
		$params = array(
			'index' => $index->getIndexName(),
			'type' => $index->getTypeName(),
            'from' => $this->offset,
            'size' => $this->limit
		);
		$params['body'] = array();

		if(($q = $this->query->parse()) && !empty($q)){
		    $params['body']['query']['filtered']['filter'] = $q;
		}

        if($this->query->getSort())
            $params['body'] = array_merge($params['body'], $this->query->getSort());

        $this->response = Haystack::getInstance()->getClient()->search($params);

		if(isset($this->response['hits']['hits']))
			$this->data = $this->response['hits']['hits'];
	}
}
