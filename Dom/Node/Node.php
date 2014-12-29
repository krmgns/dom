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
 * @object  Dom\Node\Node
 * @uses    Dom\Error
 * @version 1.0
 * @author  Kerem Gunes <qeremy@gmail>
 */
class Node
{
    /**
     * Node types
     *
     * @const int
     */
    const TYPE_ELEMENT                = 1,
          TYPE_TEXT                   = 3,
          TYPE_CDATA                  = 4,  // @added instead of TYPE_CDATA_SECTION
          TYPE_COMMENT                = 8,
          TYPE_DOCUMENT               = 9,
          TYPE_DOCUMENT_TYPE          = 10,
          // These type are not used in this programs
          TYPE_ATTRIBUTE              = 2,  // @deprecated
          TYPE_CDATA_SECTION          = 4,  // @deprecated
          TYPE_ENTITY_REFERENCE       = 5,  // @deprecated
          TYPE_ENTITY                 = 6,  // @deprecated
          TYPE_PROCESSING_INSTRUCTION = 7,
          TYPE_DOCUMENT_FRAGMENT      = 11,
          TYPE_NOTATION               = 12; // @deprecated

    /**
     * Attribute names
     *
     * @const str
     */
    const ATTRIBUTE_NAME_CLASS        = 'class',
          ATTRIBUTE_NAME_STYLE        = 'style';

    /**
     * Clone hash stack for self.isCloneOf()
     *
     * @var array
     */
    protected static $cloneHashes = array();

    /**
     * Node name
     *
     * @var str
     */
    protected $name;

    /**
     * Node value
     *
     * @var str|null
     */
    protected $value;

    /**
     * Node type
     *
     * @var int
     */
    protected $type;

    /**
     * Child nodes
     *
     * @var NodeCollection
     */
    protected $children;

    /**
     * Child nodes
     *
     * @var AttributeCollection
     */
    protected $attributes;

    /**
     * Parent node
     *
     * @var Node
     */
    protected $parent;

    /**
     * Owner document
     *
     * @var Document
     */
    protected $ownerDocument;

    /**
     * Self-closing
     *
     * Used for auto-detect self-closing html nodes
     * Could be set manually for xml nodes, see Element
     *
     * @var bool
     */
    protected $selfClosing = false;

    /**
     * Self-closing html tags
     *
     * @var array
     */
    public static $selfClosings = array(
        'area', 'base', 'br', 'col', 'command',
        'embed', 'hr', 'img', 'input', 'keygen',
        'link', 'meta', 'param', 'source', 'track', 'wbr'
    );

    /**
     * Create a new Node object
     *
     * @param str $name
     * @param str|null $value
     * @param int $type
     */
    public function __construct($name, $value = null, $type = self::TYPE_ELEMENT) {
        // Init vars
        $this->name  = strtolower($name);
        $this->value = $value;
        $this->type  = $type;
        // Except #text|comment|cdata|documentType
        if ($type == self::TYPE_ELEMENT || $type == self::TYPE_DOCUMENT) {
            // Self-closing?
            $this->selfClosing = in_array($name, self::$selfClosings);
            // Init collections
            $this->children    = new NodeCollection();
            $this->attributes  = new AttributeCollection();
        }
    }

    /**
     * Return property if exists
     *
     * @param  str $name
     * @return mix
     * @throw  Error\Property (if property does not exists)
     */
    public function __get($name) {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        throw new Error\Property('Property does not exists! name: %s', $name);
    }

    /**
     * Store clone hashes (used in self.isCloneOf())
     *
     * @return void
     */
    public function __clone() {
        self::$cloneHashes[] = spl_object_hash($this);
    }

    /**
     * Set parent node
     *
     * @param  Node|null $parent
     * @return void
     */
    public function setParent(Node $parent = null) {
        $this->parent = $parent;
    }

    /**
     * Set owner document
     *
     * @param  Document|null $ownerDocument
     * @return void
     */
    public function setOwnerDocument(Document $ownerDocument = null) {
        $this->ownerDocument = $ownerDocument;
    }

    /**
     * Check node is child of another node
     *
     * @param  Node $node
     * @return bool
     */
    public function isChildOf(Node $target) {
        return ($this->parent == $target);
    }

    /**
     * Check node is parent of another node
     *
     * @param  Node $target
     * @return bool
     */
    public function isParentOf(Node $target) {
        return ($this->children->index($target) !== null);
    }

    /**
     * Check node is clone of another node
     *
     * @param  Node $target
     * @return bool
     */
    public function isCloneOf(Node $target) {
        return ($this == $target && $this !== $target
                && in_array(spl_object_hash($this), $target::$cloneHashes));
    }

    /**
     * Check node is same node with another node
     *
     * @param  Node $target
     * @return bool
     */
    public function isSameNode(Node $target) {
        return ($this == $target);
    }

    /**
     * Check node is self-closing node
     *
     * @param  Node $target
     * @return bool
     */
    public function isSelfClosing() {
        return (bool) $this->selfClosing;
    }

    /**
     * Append a child node
     *
     * @param  Node $child
     * @return self
     */
    public function append(Node $child) {
        // Check errors
        $this->canInsert($child);
        // Set parent
        $child->setParent($this);
        // Append
        $this->children->append($child);

        return $this;
    }

    /**
     * Prepend a child node
     *
     * @param  Node $child
     * @return self
     */
    public function prepend(Node $child) {
        // Check errors
        $this->canInsert($child);
        // Set parent
        $child->setParent($this);
        // Prepend
        $this->children->prepend($node);

        return $this;
    }

    /**
     * Replace node with new node
     *
     * @param  Node $new
     * @return self
     * @throws Error\Node (if node has no parent | node's parent has not old node)
     */
    public function replace(Node $new) {
        $old = $this;
        if ($old->isSameNode($new)) {
            throw new Error\Node('These are same nodes!');
        }
        // Check errors
        if (isset($old->parent)) {
            if ($old->parent->hasChildren()) {
                // Set parent
                $new->setParent($old->parent);
                foreach ($old->parent->children as $i => $child) {
                    if ($child == $old) {
                        $old->parent->children->replace($i, $new);
                        return $new;
                    }
                }
            }
            throw new Error\Node('Parent has no child such as old node!');
        }
        throw new Error\Node('Old node has no parent!');
    }

    /**
     * Replace an old (child) node with new node
     *
     * @param  Node $old
     * @param  Node $new
     * @return self
     * @throws Error\Node (if node has not old node)
     */
    public function replaceChild(Node $old, Node $new) {
        if ($this->hasChildren()) {
            // Set parent
            $new->setParent($this);
            foreach ($this->children as $i => $child) {
                if ($child == $old) {
                    $this->children->replace($i, $new);
                    return $this;
                }
            }
            throw new Error\Node('Parent has no child such as old node!');
        }
    }

    /**
     * Prepend a sibling
     *
     * @param  Node $sibling
     * @return self
     * @throws Error\Node (if target not appended to sibling's parent before)
     */
    public function before(Node $sibling) {
        $target = $this;
        if (isset($target->parent)) {
            // Set parent
            $sibling->setParent($target->parent);
            if ($target->parent->hasChildren()) {
                foreach ($target->parent->children as $i => $child) {
                    if ($child == $target) {
                        $target->parent->children->put($i, $sibling);
                        return $target;
                    }
                }
            }
        }
        throw new Error\Node('`%s` node cannot be insterted before `%s`. '.
            'Did you append first `%s` ($target)?', $sibling->name, $target->name, $target->name);
    }

    /**
     * Append a sibling
     *
     * @param  Node $sibling
     * @return self
     * @throws Error\Node (if target not appended to sibling's parent before)
     */
    public function after(Node $sibling) {
        $target = $this;
        if (isset($target->parent)) {
            // Set parent
            $sibling->setParent($target);
            if ($target->parent->hasChildren()) {
                foreach ($target->parent->children as $i => $child) {
                    if ($child == $target) {
                        $target->parent->children->put($i + 1, $sibling);
                        return $target;
                    }
                }
            }
        }
        throw new Error\Node('`%s` node cannot be insterted after `%s`. '.
            'Did you append first `%s` ($target)?', $sibling->name, $target->name, $target->name);
    }

    /**
     * Append a child (self) to parent node
     *
     * @param  Node $parent
     * @return self
     */
    public function appendTo(Node $parent) {
        $parent->append($this);
        return $this;
    }

    /**
     * Prepend a child (self) to parent node
     *
     * @param  Node $parent
     * @return self
     */
    public function prependTo(Node $parent) {
        $parent->prepend($this);
        return $this;
    }

    /**
     * Append a child to target's parent node
     *
     * @param  Node $target
     * @return self
     * @throws Error\Node (if target has no parent)
     */
    public function appendAfter(Node $target) {
        if (isset($target->parent)) {
            // Set parent
            $this->setParent($target->parent);
            // Get index of target
            $i = (int) $target->parent->children->index($target);
            // Put it into parent's children with specific index
            $target->parent->children->put($i + 1, $this);

            return $this;
        }
        throw new Error\Node('Target node has no parent! node: `%s`', $target->name);
    }

    /**
     * Prepend a child to target's parent node
     *
     * @param  Node $target
     * @return self
     * @throws Error\Node (if target has no parent)
     */
    public function appendBefore(Node $target) {
        if (isset($target->parent)) {
            // Set parent
            $this->setParent($target->parent);
            // Get index of target
            $i = (int) $target->parent->children->index($target);
            // Put it into parent's children with specific index
            $target->parent->children->put($i, $this);

            return $this;
        }
        throw new Error\Node('Target node has no parent! node: `%s`', $target->name);
    }

    /**
     * Remove a child
     *
     * @param  Node $target
     * @return self
     * @throws Error\Node (if child not found)
     */
    public function remove(Node $child) {
        if (($i = $this->children->index($child)) !== null) {
            $this->children->del($i);
            return $this;
        }
        throw new Error\Node(
            'The node to be removed is not a child of this node. node: `%s`', $node->name);
    }

    /**
     * Append a child to target's parent node
     *
     * This method is slow, if $deep=true then it's very slow
     * Could not figure out, will be done..
     *
     * @param  bool $deep
     * @return Node
     */
    public function doClone($deep = false) {
        $doc = new Document();
        switch ($this->type) {
            case self::TYPE_ELEMENT:
                $clone = $doc->createElement($this->name);
                $clone->attributes = new AttributeCollection();
                // Attributes..
                if ($this->hasAttributes()) {
                    foreach ($this->attributes as $attribute) {
                        if ($attribute->isId()) {
                            // Thanks! <https://developer.mozilla.org/en-US/docs/Web/API/Node.cloneNode>
                            trigger_error('cloneNode() may lead to duplicate element IDs in a document.',
                                E_USER_WARNING);
                        }
                        $attribute->setOwnerElement($clone);
                        $clone->attributes->add($attribute);
                    }
                }
                // Append child nodes if deep clone
                // Use carefully cos it is slow a bit..
                if ($deep && $this->hasChildren()) {
                    foreach ($this->children as $child) {
                        // Fucking slow but still works without this.. :/
                        // $child = $child->doClone($deep);
                        $clone->append($child);
                    }
                }
                break;
            case self::TYPE_TEXT:
            case self::TYPE_CDATA:
            case self::TYPE_COMMENT:
                $clone = $doc->create($this->type, $this->getContent());
                break;
        }
        // Set owner document
        $clone->setOwnerDocument($this->ownerDocument);

        return $clone;
    }

    /**
     * Remove all children from node
     *
     * @return self
     */
    public function doEmpty() {
        if ($this->hasChildren()) {
            $this->children->delAll();
        }

        return $this;
    }

    /**
     * Shotcut for appending text nodes
     *
     * @param  str $contents
     * @return self
     */
    public function appendText($contents) {
        // Create a new Text node
        $contents = new Text($contents);
        $contents->setOwnerDocument($this->ownerDocument);
        return $this->append($contents);
    }

    /**
     * Shotcut for appending comment nodes
     *
     * @param  str $contents
     * @return self
     */
    public function appendComment($contents) {
        // Create a new Comment node
        $contents = new Comment($contents);
        $contents->setOwnerDocument($this->ownerDocument);
        return $this->append($contents);
    }

    /**
     * Shotcut for appending cdata nodes
     *
     * @param  str $contents
     * @return self
     */
    public function appendCData($contents) {
        // Create a new CData node
        $contents = new CData($contents);
        $contents->setOwnerDocument($this->ownerDocument);
        return $this->append($contents);
    }

    /**
     * Check node has children
     *
     * @return boolean
     */
    public function hasChildren() {
        return ($this->children instanceof NodeCollection &&
                $this->children->length > 0);
    }

    /**
     * Check node has attributes
     *
     * @return boolean
     */
    public function hasAttributes() {
        if ($this->attributes instanceof AttributeCollection &&
                $this->attributes->length > 0) {
            foreach ($this->attributes as $i => $attribute) {
                // Control for class/style attributes has item?
                if ((($this->isClassAttribute($attribute) || $this->isStyleAttribute($attribute))
                    && $attribute->value->length) || $i >= 2) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Add attribute object to node
     *
     * @param Attribute $attribute
     * @return self
     */
    public function setAttributeObject(Attribute $attribute) {
        $this->attributes->add($attribute);
        return $this;
    }

    /**
     * Get attribute object of node
     *
     * @param  str $name
     * @return Attribute $attribute|null
     */
    public function getAttributeObject($name) {
        foreach ($this->attributes as $attribute) {
            if ($attribute->name == $name) {
                return $attribute;
            }
        }
    }

    /**
     * Set node attribute
     *
     * @param  str $name
     * @param  mix $value
     * @return self
     */
    public function setAttribute($name, $value = null) {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->setAttribute($key, $val);
            }
            return $this;
        }
        return $this->setAttributeObject(new Attribute($name, $value, $this));
    }

    /**
     * Get node attribute value
     *
     * @param  str $name
     * @return str|null
     */
    public function getAttribute($name) {
        foreach ($this->attributes as $attribute) {
            if ($attribute->name == $name) {
                return $attribute->value;
            }
        }
    }

    /**
     * Remove node attribute
     *
     * @param  str $name
     * @return self
     */
    public function removeAttribute($name) {
        foreach ($this->attributes as $i => $attribute) {
            if ($attribute->name == $name) {
                $this->attributes->del($i);
                break;
            }
        }
        return $this;
    }

    /**
     * Check node attribute is exists
     *
     * @param  str $name
     * @return bool
     */
    public function hasAttribute($name) {
        foreach ($this->attributes as $attribute) {
            if ($attribute->name == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get child node by index
     *
     * @param  str $i
     * @return Node
     * @throws Error\Node (if index not found | seletor node is not document/element)
     */
    public function item($i) {
        if ($this->type == self::TYPE_DOCUMENT || $this->type == self::TYPE_ELEMENT) {
            if ($this->children->has($i)) {
                return $this->children->item($i);
            }
            throw new Error\Node('Item index not found! item: %s', $i);
        }
        throw new Error\Node('Not suppoerted node to select! node: %s', $this->name);
    }

    /**
     * Get first child
     *
     * @return self.item()
     */
    public function first() {
        return $this->item(0);
    }

    /**
     * Get last child
     *
     * @return self.item()
     */
    public function last() {
        return $this->item($this->children->length - 1);
    }

    /**
     * Get previous sibling
     *
     * @return Node|null
     */
    public function prev() {
        if ($this->parent->hasChildren()) {
            foreach ($this->parent->children as $i => $child) {
                if ($child == $this && $this->parent->children->has($i - 1)) {
                    return $this->parent->children->item($i - 1);
                }
            }
        }
    }

    /**
     * Get next sibling
     *
     * @return Node|null
     */
    public function next() {
        if ($this->parent->hasChildren()) {
            foreach ($this->parent->children as $i => $child) {
                if ($child == $this && $this->parent->children->has($i + 1)) {
                    return $this->parent->children->item($i + 1);
                }
            }
        }
    }

    /**
     * Get previous siblings
     *
     * @return NodeCollection|null
     */
    public function prevAll() {
        if ($this->parent->hasChildren()) {
            $collection = new NodeCollection();
            foreach ($this->parent->children as $child) {
                if ($child == $this) {
                    break;
                }
                $collection->add($child);
            }
            return $collection;
        }
    }

    /**
     * Get next siblings
     *
     * @return NodeCollection|null
     */
    public function nextAll() {
        if ($this->parent->hasChildren()) {
            $collection = new NodeCollection();
            $found = !true;
            foreach ($this->parent->children as $child) {
                if (!$found && $child == $this) {
                    $found = true;
                }
                $found && $child != $this && $collection->add($child);
            }
            return $collection;
        }
    }

    /**
     * Get siblings
     *
     * @return NodeCollection|null
     */
    public function siblings() {
        if ($this->parent->hasChildren()) {
            $collection = new NodeCollection();
            foreach ($this->parent->children as $child) {
                if ($child != $this) {
                    $collection->add($child);
                }
            }
            return $collection;
        }
    }
    // @notimplemented
    public function find($selector) {}

    /**
     * Set inner text (aka textContent)
     *
     * This method will not parse and create/append $contents as child nodes
     * into the target ($this) node for now, but in the future...
     *
     * @param strint $contents
     */
    public function setInnerText($contents) {
        $this->doEmpty();
        $this->appendText($contents);
        return $this;
    }

    /**
     * Get inner text (aka textContent)
     *
     * @return str
     */
    public function getInnerText() {
        return strip_tags($this->toString());
    }

    // @notimplemented
    public function setInnerHtml($contents) {}
    public function getInnerHtml() {}

    /**
     * Path get implementation like DOMNode::getNodePath
     *
     * @link   http://php.net/manual/en/domnode.getnodepath.php
     * @return str Node path
     */
    public function getPath() {
        // Set first self name
        $path = array(-1 => $this->name);

        // Set path as array look, e.g #document/html/body/div/p[0]
        static $i = 0;
        if ($siblings = $this->siblings()) {
            foreach ($siblings as $sibling) {
                if ($sibling->name == $this->name) {
                    // Overwrite
                    $path[-1] = sprintf('%s[%d]', $this->name, $i++);
                }
            }
        }

        // Get parent
        $parent = $this->parent;
        // Upward recursion
        while ($parent != null) {
            $path[] = $parent->name;
            // Next
            $parent = $parent->parent;
        }

        // Return path
        return join('/', array_reverse($path));
    }

    /**
     * Output generator
     *
     * @return str (innerText/innerHtml)
     */
    public function toString() {
        $string = '';
        // Add doctype xml/html
        if ($this->type == self::TYPE_DOCUMENT) {
            if ($this->doctype->name == Document::DOCTYPE_HTML) {
                $string .= sprintf("<!DOCTYPE %s>\r\n",
                    $this->doctype->name);
            } elseif ($this->doctype->name == Document::DOCTYPE_XML) {
                $string .= sprintf("<?xml version=\"%s\" encoding=\"%s\"?>%s\r\n",
                    $this->version, $this->encoding);
            }
        }
        // Child nodes
        foreach ($this->children as $node) {
            // Prepare nodes as string
            switch ($node->type) {
                // Add #text|comment|cdata nodes
                case self::TYPE_TEXT:
                case self::TYPE_CDATA:
                case self::TYPE_COMMENT:
                   $string .= $node->getContent();
                   break;
                // Add element contents
                case self::TYPE_ELEMENT:
                    // Prepare attributes as string
                    $attributes = '';
                    if ($node->hasAttributes()) {
                        // Overwrite to join
                        $attributes = array();
                        foreach ($node->attributes as $attribute) {
                            // Append classes
                            if ($this->isClassAttribute($attribute)) {
                                if ($classText = $node->getClassText()) {
                                    $attributes[] = sprintf('class="%s"', $classText);
                                }
                                continue;
                            }
                            // Append styles
                            if ($this->isStyleAttribute($attribute)) {
                                if ($styleText = $node->getStyleText()) {
                                    $attributes[] = sprintf('style="%s"', $styleText);
                                }
                                continue;
                            }
                            $attributes[] = sprintf('%s="%s"', $attribute->name, $attribute->value);
                        }
                        // Pass empty class/style count
                        $attributes = count($attributes)
                            ? ' '. join(' ', $attributes)
                            : '';
                    }
                    // Check is self closing tag or not
                    if ($node->selfClosing) {
                        $string .= sprintf('<%s%s />', $node->name, $attributes);
                    } else {
                        $string .= sprintf('<%s%s>', $node->name, $attributes);
                        // Do recursion if has children
                        if ($node->hasChildren()) {
                            $string .= $node->toString();
                        }
                        $string .= sprintf('</%s>', $node->name);
                    }
                    break;
            }
        }

        return $string;
    }

    /**
     * Check target node ($this) can istert the given node as child
     *
     * @param  Node $node
     * @return bool
     * @throws Error\Node (see messages)
     */
    protected function canInsert(Node $node) {
        /**
         * Leaving this here..
         * $debug = debug_backtrace();
         * if (isset($debug[1])) {
         *     $callee = "{$debug[1]['class']}::{$debug[1]['function']}";
         *     $callee = substr($callee, strrpos($callee, ':')+1);
         *     printf("[$callee:  node <$node->name> --- this <$this->name>\n");
         * }
         */

        // WTF the hierarchy rules.. @mustbeextended
        if ($node->type == self::TYPE_ELEMENT && ($this->type == self::TYPE_TEXT ||
            $this->type == self::TYPE_COMMENT || $this->type == self::TYPE_DOCUMENT_TYPE)) {
            throw new Error\Node('No insert operations into #text, #comment and #documentType');
        }

        if ($this->isSameNode($node)) {
            throw new Error\Node('No insert operations into same node!');
        }

        if ($this->isChildOf($node)) {
            throw new Error\Node('No insert operations into child node!');
        }

        if ($this->isSelfClosing()
            // || ($node->isSelfClosing() && $this->type != self::TYPE_ELEMENT)
            // || ($this->isSelfClosing() && $node->type != self::TYPE_ELEMENT)
        ) {
            throw new Error\Node('No insert operations self closing node!');
        }

        // This is trivial but stands for future devs
        return true;
    }

    /**
     * Check attribute whether class or not
     *
     * @param  Attribute $attribute
     * @return bool
     */
    protected function isClassAttribute($attribute) {
        return ($attribute->name == self::ATTRIBUTE_NAME_CLASS
                    && $attribute->value instanceof ClassCollection);
    }

    /**
     * Check attribute whether style or not
     *
     * @param  Attribute $attribute
     * @return bool
     */
    protected function isStyleAttribute($attribute) {
        return ($attribute->name == self::ATTRIBUTE_NAME_STYLE
                    && $attribute->value instanceof StyleCollection);
    }
}


/**
 * End of file.
 *
 * @file /dom/Dom/Node/Node.php
 * @tabs Space=4 (Sublime Text 3)
 */
