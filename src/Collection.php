<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Kerem Gunes
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Dom;

use Dom\Error;

/**
 * @package Dom
 * @object  Dom\Collection
 * @author  Kerem Gunes <k-gun@mail.com>
 */
class Collection
    implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * Count of items.
     * @var integer
     */
    protected $length = 0;

    /**
     * Items stack.
     * @var array
     */
    protected $items = array();


    /**
     * Create a new Collection object.
     *
     * @param array $items
     */
    public function __construct(array $items = null) {
        // set items
        if (!empty($items)) {
            foreach ($items as $item) {
                $this->add($item);
            }
        }
    }

    /**
     * Always throw an exception.
     *
     * @param  string  $name
     * @param  mixed   $value
     * @throws Dom\Error\Property
     * @return void
     */
    public function __set($name, $value) {
        throw new Error\Property(
            'You cannot set properties dynamically on this class! class: %s', get_class($this));
    }

    /**
     * Return property if exists.
     *
     * @param  string $name
     * @throws Dom\Error\Property
     * @return mixed
     */
    public function __get($name) {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        throw new Error\Property('Property does not exists! name: %s', $name);
    }

    /**
     * Add new item.
     *
     * @param  mixed  $item
     * @throws Dom\Error
     * @return self
     */
    public function add($item) {
        return $this->offsetSet($this->length, $item);
    }

    /**
     * Remove an item with specific index.
     *
     * @param  integer $i
     * @return self
     */
    public function del($i) {
        return $this->offsetUnset($i);
    }

    /**
     * Remove all items.
     *
     * @return self
     */
    public function delAll() {
        foreach ($this->items as $i => $item) {
            $this->del($i);
        }

        return $this;
    }

    /**
     * Check items exists or not.
     *
     * @param  integer $i
     * @return boolean
     */
    public function has($i) {
        return $this->offsetExists($i);
    }

    /**
     * Return item if exists.
     *
     * @param  integer $i
     * @return mixed
     */
    public function item($i) {
        return $this->offsetGet($i);
    }

    /**
     * Return items.
     *
     * @return array
     */
    public function toArray() {
        return $this->items;
    }

    /**
     * Pop an item.
     *
     * @return mixed
     */
    public function pop() {
        $return = array_pop($this->items);
        $this->updateLength();

        return $return;
    }

    /**
     * Shift an item.
     *
     * @return mixed
     */
    public function shift() {
        $return = array_shift($this->items);
        $this->updateLength();

        return $return;
    }

    /**
     * Put an item to items with specific index.
     *
     * @param  integer $i
     * @param  mixed   $item
     * @return self
     */
    public function put($i, $item) {
        array_splice($this->items, abs($i), 0, array($item));
        $this->updateLength();

        return $this;
    }

    /**
     * Add an item to the beginning.
     *
     * @param  mixed $item
     * @return self
     */
    public function append($item) {
        $this->items = array_merge($this->items, array($item));
        $this->updateLength();

        return $this;
    }

    /**
     * Add an item onto the end.
     *
     * @param  mixed $item
     * @return self
     */
    public function prepend($item) {
        array_unshift($this->items, $item);
        $this->updateLength();

        return $this;
    }

    /**
     * Remove duplicates from items.
     *
     * @return self
     */
    public function unique() {
        $this->items = array_unique($this->items);
        $this->updateLength();

        return $this;
    }

    /**
     * Filter items using callback.
     *
     * @param  \Closure $callback
     * @return self
     */
    public function filter(\Closure $callback) {
        $this->items = array_filter($this->items, function($item) use($callback) {
            return $callback($item);
        });

        $this->updateLength();

        return $this;
    }

    /**
     * Replace an item with specific index.
     *
     * @param  integer $i
     * @param  mixed   $newItem
     * @throws Dom\Error
     * @return self
     */
    public function replace($i, $newItem) {
        foreach ($this->items as $ii => $item) {
            if ($i == $ii) {
                $this->items[$i] = $newItem;
                return $this;
            }
        }
        throw new Error('Item index does not exists! index: %d', $i);
    }

    /**
     * Return the matched index if item found in items.
     *
     * @param  mixed $srcItem
     * @return int|null
     */
    public function index($srcItem) {
        $srcItemType = gettype($srcItem);
        foreach ($this->items as $i => $item) {
            if ($srcItemType == gettype($item) && $srcItem == $item) {
                return $i;
            }
        }

        return null;
    }

    /**
     * Abstract method of \Countable.
     *
     * @return integer
     */
    public function count() {
        return count($this->items);
    }

    /**
     * Abstract method of \IteratorAggregate.
     *
     * @return \ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->items);
    }

    /**
     * Abstract method of ArrayAccess.
     *
     * @param  integer $i
     * @param  mixed   $item
     * @throws Dom\Error
     * @return self
     */
    public function offsetSet($i, $item) {
        if ($this->offsetExists($i)) {
            throw new Error('Item index already exists! index: %d', $i);
        }

        $this->items[$i] = $item;
        $this->updateLength();

        return $this;
    }

    /**
     * Abstract method of ArrayAccess.
     *
     * @param  integer $i
     * @throws Dom\Error
     * @return mixed
     */
    public function offsetGet($i) {
        if (!$this->offsetExists($i)) {
            throw new Error('Item index does not exists! index: %d', $i);
        }

        return $this->items[$i];
    }

    /**
     * Abstract method of ArrayAccess.
     *
     * @param  integer $i
     * @return self
     */
    public function offsetUnset($i) {
        unset($this->items[$i]);
        $this->updateLength();

        return $this;
    }

    /**
     * Abstract method of ArrayAccess.
     *
     * @param  integer $i
     * @return boolean
     */
    public function offsetExists($i) {
        return isset($this->items[$i]);
    }

    /**
     * Update collection length after each modify action.
     *
     * @return void
     */
    private function updateLength() {
        $this->length = $this->count();
    }
}
