<?php
namespace Haystack;
/**
 * Haystack index classes are used to configure your indexes in the search engine and allow
 * you to manage how your data gets indexed.
 *
 * @package Haystack
 * @author Chris Santos <csantosdev@gmail.com>
 */
abstract class Index
{
    /**
     * @var \Haystack\Engines\Engine;
     */
    private $engine;

    /**
     * Instance of a Haystack Index class.
     *
     * @param Engines\Engine $engine
     */
    public function __construct(Engines\Engine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Returns the Engine instance being used for this Haystack index instance.
     *
     * @return \Haystack\Engines\Engine
     */
    final public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Returns the name that should be used when creating the index.
     *
     * @return string
     */
    abstract public function getIndexName();

    /**
     * Returns the class name of the Model object that this index represents.
     *
     * @return string
     */
    abstract public function getModelClass();

    /**
     * Returns the default mapping configuration for the index when creating it.
     *
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return $this->engine->getDefaultIndexConfiguration();
    }
}