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

namespace Dom\Node;

use \Dom\Error;

/**
 * @package Dom\Node
 * @object  Dom\Node\Element
 * @extends Dom\Node
 * @uses    Dom\Error
 * @version 1.0
 * @author  Kerem Gunes <qeremy@gmail>
 */
class Element
    extends Node
{
    /**
     * Id attribute property
     *
     * @var str
     */
    protected $id;

    /**
     * Tag name property
     *
     * @var str
     */
    protected $tag;

    /**
     * Create a new Element object
     *
     * @param  str        $tag         (tagName)
     * @param  array|null $attributes
     * @param  str        $text        (innerText aka textContent)
     * @param  bool       $selfClosing (used for manual self closing option for xml nodes)
     * @return self
     */
    public function __construct($tag, $value = null, array $attributes = null, $selfClosing = null) {
        // Check tag name
        if (!preg_match('~^[\w-]+$~i', $tag)) {
            throw new Error\Node('Not proper tag name!');
        }
        // Set tag name
        $this->tag = $tag;

        // Call parent initor
        parent::__construct($tag, $value, Node::TYPE_ELEMENT);

        // Overwrite selfClosing if provided
        // Generally used for xml nodes that contains no body
        if (is_bool($selfClosing)) {
            $this->selfClosing = $selfClosing;
        }

        // Init class/style attributes
        if (!isset($this->attributes->class)) {
            $this->setAttribute(self::ATTRIBUTE_NAME_CLASS, new ClassCollection());
        }
        if (!isset($this->attributes->style)) {
            $this->setAttribute(self::ATTRIBUTE_NAME_STYLE, new StyleCollection());
        }

        // Set attributes if provided
        if (!empty($attributes)) {
            foreach ($attributes as $name => $value) {
                // Set id
                if (!isset($this->id) && $name == 'id') {
                    $this->id = $value;
                }
                // Set classes/styles/attributes
                if ($name == self::ATTRIBUTE_NAME_CLASS) {
                    $this->addClass($value);
                } elseif ($name == self::ATTRIBUTE_NAME_STYLE) {
                    $this->setStyle($value);
                } else {
                    $this->setAttribute($name, $value);
                }
            }
        }
    }

    /**
     * Check class exists
     *
     * @param  str  $name
     * @return bool
     */
    public function hasClass($name) {
        return in_array($name, $this->getClassCollection()->toArray());
    }

    /**
     * Add new class to element (remove duplicate classes)
     *
     * @param self
     */
    public function addClass($value) {
        // Value could be array
        if (is_array($value)) {
            foreach ($value as $val) {
                $this->addClass($val);
            }
            return $this;
        }

        // If class passed with spaces
        if (strpos($value, ' ') !== false) {
            $value = preg_split('~\s+~', $value, -1, PREG_SPLIT_NO_EMPTY);
            return $this->addClass($value);
        }

        // Add class into ClassCollection and remove duplicates
        $this->getClassCollection()->append($value)->unique();

        return $this;
    }

    /**
     * Remove a class from element
     *
     * @param  str $name
     * @return self
     */
    public function removeClass($name) {
        $this->getClassCollection()->filter(function($class) use($name) {
            return ($class != $name);
        });

        return $this;
    }

    /**
     * Get (all) class text
     *
     * @return str
     */
    public function getClassText() {
        return join(' ', $this->getClassCollection()->toArray());
    }

    /**
     * Get element's ClassCollection
     *
     * @return ClassCollection
     * @throws Error\Instance (if no classes)
     */
    public function getClassCollection() {
        foreach ($this->attributes as $attribute) {
            if ($this->isClassAttribute($attribute)) {
                return $attribute->value;
            }
        }
        throw new Error\Instance(
            'Class attributes must be instance of Dom\\Node\\ClassCollection');
    }

    /**
     * Set style (if already exists replace new value)
     *
     * @param str|array $name
     * @param mix self
     */
    public function setStyle($name, $value = null) {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->setStyle($key, $val);
            }
            return $this;
        }
        // Remove style
        $this->removeStyle($name);
        // Add style
        $this->getStyleCollection()->add(new Style($name, $value, $this));

        return $this;
    }

    /**
     * Get style value
     *
     * @param  str $name
     * @return str|null
     */
    public function getStyle($name) {
        foreach ($this->getStyleCollection() as $style) {
            if ($style->name == $name) {
                return $style->value;
            }
        }
    }

    /**
     * Remove style from list
     *
     * @param  str $name
     * @return Element
     */
    public function removeStyle($name) {
        $styles = $this->getStyleCollection();
        // Delete all
        if ($name == '*') {
            $styles->delAll();
        } else {
            // Find and delete style
            foreach ($styles as $i => $style) {
                if ($style->name == $name) {
                    $styles->del($i);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Get plain style text (e.g: "color: #fff; width: 10px;")
     *
     * @return str
     */
    public function getStyleText() {
        $text   = '';
        foreach ($this->getStyleCollection() as $style) {
            $text .= ' '. $style->toString();
        }

        return trim($text);
    }

    /**
     * Get style collection
     *
     * @return StyleCollection
     * @throws Error\Instance (if no styles)
     */
    public function getStyleCollection() {
        foreach ($this->attributes as $attribute) {
            if ($this->isStyleAttribute($attribute)) {
                return $attribute->value;
            }
        }
        throw new Error\Instance(
            'Style attributes must be instance of Dom\\Node\\StyleCollection');
    }
}

/**
 * End of file.
 *
 * @file /dom/Dom/Node/Element.php
 * @tabs Space=4 (Sublime Text 3)
 */
