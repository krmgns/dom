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

namespace Dom\Shablon\Node;

use \Dom\Error;
use \Dom\Node\Node;
use \Dom\Node\Element;

/**
 * @package Dom\Shablon\Node
 * @object  Dom\Shablon\Node\ElementProperty
 * @uses    Dom\Error, Dom\Node, Dom\Node\Element
 * @version 1.1
 * @author  Kerem Gunes <qeremy@gmail>
 *
 * @abstract
 */
abstract class ElementProperty
{
    /**
     * Name of property
     * @var str
     */
    protected $name;

    /**
     * Type of property
     * @var mix
     */
    protected $value;

    /**
     * Owner element of property
     * @var Element
     */
    protected $ownerElement;

    /**
     * Create a new Element object
     *
     * @param str $name
     * @param mix $value
     * @param Element|null $ownerElement
     */
    public function __construct($name, $value, Element $ownerElement = null) {
        // Set value as string
        if ($name != Node::ATTRIBUTE_NAME_CLASS &&
            $name != Node::ATTRIBUTE_NAME_STYLE) {
            $value = (string) $value;
        }
        // Set properties
        $this->name  = $name;
        $this->value = $value;

        // Set owner element
        if ($ownerElement) {
            $this->setOwnerElement($ownerElement);
        }
    }

    /**
     * Always throws exception
     *
     * @param  str  $name
     * @param  mix  $value
     * @return void
     * @throw  Error\Property (if any property directly set)
     */
    public function __set($name, $value) {
        throw new Error\Property(
            'You cannot set properties dynamically on this class! class: %s', get_class($this));
    }

    /**
     * Returns property if exists
     *
     * @param  str $name
     * @return mix
     * @throw  Error\Property (if property does not exists)
     */
    public function __get($name) {
        if (in_array($name, array('name', 'value'))) {
            return $this->$name;
        }
        throw new Error\Property('Property does not exists! name: %s', $name);
    }

    /**
     * Set owner Element
     *
     * @param  Element|null $ownerElement
     * @return void
     */
    public function setOwnerElement(Element $ownerElement = null) {
        $this->ownerElement = $ownerElement;
    }

    /**
     * Get owner Element
     *
     * @return Element
     */
    public function getOwnerElement() {
        return $this->ownerElement;
    }

    /**
     * Get property name
     *
     * @return str
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get property value
     *
     * @return mix
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Return property name/value
     *
     * @return string
     */
    abstract public function toString();
}

/**
 * End of file.
 *
 * @file /dom/Dom/Shablon/ElementProperty.php
 * @tabs Space=4 (Sublime Text 3)
 */
