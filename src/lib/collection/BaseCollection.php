<?php
/**
 * Created by solly [18.10.17 11:05]
 */

namespace insolita\codestat\lib\collection;

use ArrayAccess;
use ArrayIterator;

class BaseCollection implements ArrayAccess, \IteratorAggregate, \Countable
{
    protected $items = [];
    
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
    
    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }
    
    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }
    
    /**
     * Get an item at a given offset.
     *
     * @param  mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }
    
    /**
     * Set the item at a given offset.
     *
     * @param  mixed $key
     * @param  mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }
    
    /**
     * Unset the item at a given offset.
     *
     * @param  string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }
    
}
