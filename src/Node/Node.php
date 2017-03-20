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

use Dom\Error;

/**
 * @package Dom\Node
 * @object  Dom\Node\Node
 * @author  Kerem Gunes <k-gun@mail.com>
 */
class Node
{
    /**
     * Node types.
     * @const integer
     */
    const TYPE_ELEMENT                = 1,
          TYPE_TEXT                   = 3,
          TYPE_CDATA                  = 4,  // @added instead of TYPE_CDATA_SECTION
          TYPE_COMMENT                = 8,
          TYPE_DOCUMENT               = 9,
          TYPE_DOCUMENT_TYPE          = 10,
          // these type are not used in this programs
          TYPE_ATTRIBUTE              = 2,  // @deprecated
          TYPE_CDATA_SECTION          = 4,  // @deprecated
          TYPE_ENTITY_REFERENCE       = 5,  // @deprecated
          TYPE_ENTITY                 = 6,  // @deprecated
          TYPE_PROCESSING_INSTRUCTION = 7,
          TYPE_DOCUMENT_FRAGMENT      = 11,
          TYPE_NOTATION               = 12; // @deprecated

    /**
     * Attribute names.
     * @const string
     */
    const ATTRIBUTE_NAME_CLASS        = 'class',
          ATTRIBUTE_NAME_STYLE        = 'style';

    /**
     * Clone hash stack for self.isCloneOf().
     * @var array
     */
    protected static $cloneHashes = array();

    /**
     * Node name.
     * @var string
     */
    protected $name;

    /**
     * Node value
     * @var string|null
     */
    protected $value;

    /**
     * Node type
     *
     * @var integer
     */
    protected $type;

    /**
     * Child nodes.
     * @var NodeCollection
     */
    protected $children;

    /**
     * Child nodes.
     * @var AttributeCollection
     */
    protected $attributes;

    /**
     * Parent node.
     * @var Node
     */
    protected $parent;

    /**
     * Owner document.
     * @var Document
     */
    protected $ownerDocument;

    /**
     * Self-closing. Used for auto-detecting self-closing html
     * nodes could be set manually for xml nodes, see Element
     * @var boolean
     */
    protected $selfClosing = false;

    /**
     * Self-closing HTML tags.
     * @var array
     */
    public static $selfClosings = array(
        'area', 'base', 'br', 'col', 'command',
        'embed', 'hr', 'img', 'input', 'keygen',
        'link', 'meta', 'param', 'source', 'track', 'wbr'
    );

    /**
     * Create a new Node object.
     *
     * @param string      $name
     * @param string|null $value
     * @param integer     $type
     */
    public function __construct($name, $value = null, $type = self::TYPE_ELEMENT) {
        // init vars
        $this->name  = strtolower($name);
        $this->value = $value;
        $this->type  = $type;
        // except #text|comment|cdata|documenttype
        if ($type == self::TYPE_ELEMENT || $type == self::TYPE_DOCUMENT) {
            // self-closing?
            $this->selfClosing = in_array($name, self::$selfClosings);
            // init collections
            $this->children    = new NodeCollection();
            $this->attributes  = new AttributeCollection();
        }
    }

    /**
     * Return property if exists.
     *
     * @param  string $name
     * @throws Error\Property
     * @return mixed
     */
    public function __get($name) {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new Error\Property('Property does not exists! name: %s', $name);
    }

    /**
     * Store clone hashes, used in self.isCloneOf().
     *
     * @return void
     */
    public function __clone() {
        self::$cloneHashes[] = spl_object_hash($this);
    }

    /**
     * Set parent node.
     *
     * @param  Node|null $parent
     * @return void
     */
    public function setParent(Node $parent = null) {
        $this->parent = $parent;
    }

    /**
     * Set owner document.
     *
     * @param  Document|null $ownerDocument
     * @return void
     */
    public function setOwnerDocument(Document $ownerDocument = null) {
        $this->ownerDocument = $ownerDocument;
    }

    /**
     * Check node is child of another node.
     *
     * @param  Node $node
     * @return boolean
     */
    public function isChildOf(Node $target) {
        return ($this->parent == $target);
    }

    /**
     * Check node is parent of another node.
     *
     * @param  Node $target
     * @return boolean
     */
    public function isParentOf(Node $target) {
        return ($this->children->index($target) !== null);
    }

    /**
     * Check node is clone of another node.
     *
     * @param  Node $target
     * @return boolean
     */
    public function isCloneOf(Node $target) {
        return ($this == $target && $this !== $target
                && in_array(spl_object_hash($this), $target::$cloneHashes));
    }

    /**
     * Check node is same node with another node.
     *
     * @param  Node $target
     * @return boolean
     */
    public function isSameNode(Node $target) {
        return ($this == $target);
    }

    /**
     * Check node is self-closing node.
     *
     * @param  Node $target
     * @return boolean
     */
    public function isSelfClosing() {
        return (bool) $this->selfClosing;
    }

    /**
     * Append a child node.
     *
     * @param  Node $child
     * @return self
     */
    public function append(Node $child) {
        // check errors
        $this->canInsert($child);
        // set parent
        $child->setParent($this);
        // append
        $this->children->append($child);

        return $this;
    }

    /**
     * Prepend a child node.
     *
     * @param  Node $child
     * @return self
     */
    public function prepend(Node $child) {
        // check errors
        $this->canInsert($child);
        // set parent
        $child->setParent($this);
        // prepend
        $this->children->prepend($node);

        return $this;
    }

    /**
     * Replace node with new node.
     *
     * @param  Node $new
     * @throws Dom\Error\Node
     * @return self
     */
    public function replace(Node $new) {
        $old = $this;
        if ($old->isSameNode($new)) {
            throw new Error\Node('These are same nodes!');
        }

        // check errors
        if (isset($old->parent)) {
            if ($old->parent->hasChildren()) {
                // set parent
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
     * Replace an old (child) node with new node.
     *
     * @param  Node $old
     * @param  Node $new
     * @throws Dom\Error\Node
     * @return self
     */
    public function replaceChild(Node $old, Node $new) {
        if ($this->hasChildren()) {
            // set parent
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
     * Prepend a sibling.
     *
     * @param  Node $sibling
     * @throws Dom\Error\Node
     * @return self
     */
    public function before(Node $sibling) {
        $target = $this;
        if (isset($target->parent)) {
            // set parent
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
     * Append a sibling.
     *
     * @param  Node $sibling
     * @throws Dom\Error\Node
     * @return self
     */
    public function after(Node $sibling) {
        $target = $this;
        if (isset($target->parent)) {
            // set parent
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
     * Append a child (self) to parent node.
     *
     * @param  Node $parent
     * @return self
     */
    public function appendTo(Node $parent) {
        $parent->append($this);

        return $this;
    }

    /**
     * Prepend a child (self) to parent node.
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
     * @throws Dom\Error\Node
     * @return self
     */
    public function appendAfter(Node $target) {
        if (isset($target->parent)) {
            // set parent
            $this->setParent($target->parent);
            // get index of target
            $i = (int) $target->parent->children->index($target);
            // put it into parent's children with specific index
            $target->parent->children->put($i + 1, $this);

            return $this;
        }

        throw new Error\Node('Target node has no parent! node: `%s`', $target->name);
    }

    /**
     * Prepend a child to target's parent node.
     *
     * @param  Node $target
     * @throws Dom\Error\Node
     * @return self
     */
    public function appendBefore(Node $target) {
        if (isset($target->parent)) {
            // set parent
            $this->setParent($target->parent);
            // get index of target
            $i = (int) $target->parent->children->index($target);
            // put it into parent's children with specific index
            $target->parent->children->put($i, $this);

            return $this;
        }

        throw new Error\Node('Target node has no parent! node: `%s`', $target->name);
    }

    /**
     * Remove a child.
     *
     * @param  Node $target
     * @throws Dom\Error\Node
     * @return self
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
     * Append a child to target's parent node.
     *
     * Notice: This method is slow, if $deep=true then it's
     * very slow. Could not figure out, will be done..
     *
     * @return Node
     * @param  boolean $deep
     */
    public function doClone($deep = false) {
        $doc = new Document();
        switch ($this->type) {
            case self::TYPE_ELEMENT:
                $clone = $doc->createElement($this->name);
                $clone->attributes = new AttributeCollection();
                // attributes..
                if ($this->hasAttributes()) {
                    foreach ($this->attributes as $attribute) {
                        if ($attribute->isId()) {
                            // thanks! https://developer.mozilla.org/en-US/docs/Web/API/Node.cloneNode
                            trigger_error('cloneNode() may lead to duplicate element IDs in a document.',
                                E_USER_WARNING);
                        }
                        $attribute->setOwnerElement($clone);
                        $clone->attributes->add($attribute);
                    }
                }
                // append child nodes if deep clone
                // use carefully cos it is slow a bit..
                if ($deep && $this->hasChildren()) {
                    foreach ($this->children as $child) {
                        // fucking slow but still works without this.. :/
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
        // set owner document
        $clone->setOwnerDocument($this->ownerDocument);

        return $clone;
    }

    /**
     * Remove all children from node.
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
     * Shotcut for appending text nodes.
     *
     * @param  string $contents
     * @return self
     */
    public function appendText($contents) {
        // create a new text node
        $contents = new Text($contents);
        $contents->setOwnerDocument($this->ownerDocument);

        return $this->append($contents);
    }

    /**
     * Shotcut for appending comment nodes.
     *
     * @param  string $contents
     * @return self
     */
    public function appendComment($contents) {
        // create a new comment node
        $contents = new Comment($contents);
        $contents->setOwnerDocument($this->ownerDocument);

        return $this->append($contents);
    }

    /**
     * Shotcut for appending cdata nodes.
     *
     * @param  string $contents
     * @return self
     */
    public function appendCData($contents) {
        // create a new cdata node
        $contents = new CData($contents);
        $contents->setOwnerDocument($this->ownerDocument);

        return $this->append($contents);
    }

    /**
     * Check node has children.
     *
     * @return boolean
     */
    public function hasChildren() {
        return ($this->children instanceof NodeCollection &&
                $this->children->length > 0);
    }

    /**
     * Check node has attributes.
     *
     * @return boolean
     */
    public function hasAttributes() {
        if ($this->attributes instanceof AttributeCollection &&
                $this->attributes->length > 0) {
            foreach ($this->attributes as $i => $attribute) {
                // control for class/style attributes has item?
                if ((($this->isClassAttribute($attribute) || $this->isStyleAttribute($attribute))
                    && $attribute->value->length) || $i >= 2) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add attribute object to node.
     *
     * @param  Attribute $attribute
     * @return self
     */
    public function setAttributeObject(Attribute $attribute) {
        $this->attributes->add($attribute);

        return $this;
    }

    /**
     * Get attribute object of node.
     *
     * @param  string    $name
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
     * Set node attribute.
     *
     * @param  string $name
     * @param  mixed  $value
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
     * Get node attribute value.
     *
     * @param  string $name
     * @return string|null
     */
    public function getAttribute($name) {
        foreach ($this->attributes as $attribute) {
            if ($attribute->name == $name) {
                return $attribute->value;
            }
        }
    }

    /**
     * Remove node attribute.
     *
     * @param  string $name
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
     * Check node attribute is exists.
     *
     * @param  string $name
     * @return boolean
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
     * Get child node by index.
     *
     * @param  integer $i
     * @throws Dom\Error\Node
     * @return Node
     */
    public function item($i) {
        // selector node is not document/element?
        if ($this->type == self::TYPE_DOCUMENT || $this->type == self::TYPE_ELEMENT) {
            if ($this->children->has($i)) {
                return $this->children->item($i);
            }

            throw new Error\Node('Item index not found! item: %s', $i);
        }

        throw new Error\Node('Not suppoerted node to select! node: %s', $this->name);
    }

    /**
     * Get first child.
     *
     * @return self.item()
     */
    public function first() {
        return $this->item(0);
    }

    /**
     * Get last child.
     *
     * @return self.item()
     */
    public function last() {
        return $this->item($this->children->length - 1);
    }

    /**
     * Get previous sibling.
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
     * Get next sibling.
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
     * Get previous siblings.
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
     * Get next siblings.
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
     * Get siblings.
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
     * Set inner text (aka textContent).
     *
     * Notice: This method will not parse and create/append $contents as
     * child nodes into the target ($this) node for now, but in the future..
     *
     * @param string $contents
     */
    public function setInnerText($contents) {
        $this->doEmpty()->appendText($contents);

        return $this;
    }

    /**
     * Get inner text (aka textContent).
     *
     * @return string
     */
    public function getInnerText() {
        return strip_tags($this->toString());
    }

    // @notimplemented
    public function setInnerHtml($contents) {}
    public function getInnerHtml() {}

    /**
     * Path get implementation like PHP DOMNode::getNodePath().
     * <http://php.net/manual/en/domnode.getnodepath.php>
     *
     * @return string
     */
    public function getPath() {
        // set first self name
        $path = array(-1 => $this->name);

        // set path as array look, e.g #document/html/body/div/p[0]
        static $i = 0;
        if ($siblings = $this->siblings()) {
            foreach ($siblings as $sibling) {
                if ($sibling->name == $this->name) {
                    // overwrite
                    $path[-1] = sprintf('%s[%d]', $this->name, $i++);
                }
            }
        }

        // get parent
        $parent = $this->parent;
        // upward recursion
        while ($parent != null) {
            $path[] = $parent->name;
            // next
            $parent = $parent->parent;
        }

        // return path
        return join('/', array_reverse($path));
    }

    /**
     * Output generator.
     *
     * @return string
     */
    public function toString() {
        $string = '';
        // add doctype xml/html
        if ($this->type == self::TYPE_DOCUMENT) {
            if ($this->doctype->name == Document::DOCTYPE_HTML) {
                $string .= sprintf("<!DOCTYPE %s>\r\n", $this->doctype->name);
            } elseif ($this->doctype->name == Document::DOCTYPE_XML) {
                $string .= sprintf("<?xml version=\"%s\" encoding=\"%s\"?>\r\n", $this->version, $this->encoding);
            }
        }

        // child nodes
        foreach ($this->children as $node) {
            // prepare nodes as string
            switch ($node->type) {
                // add #text|comment|cdata nodes
                case self::TYPE_TEXT:
                case self::TYPE_CDATA:
                case self::TYPE_COMMENT:
                   $string .= $node->getContent();
                   break;
                // add element contents
                case self::TYPE_ELEMENT:
                    // prepare attributes as string
                    $attributes = '';
                    if ($node->hasAttributes()) {
                        // overwrite to join
                        $attributes = array();
                        foreach ($node->attributes as $attribute) {
                            // append classes
                            if ($this->isClassAttribute($attribute)) {
                                if ($classText = $node->getClassText()) {
                                    $attributes[] = sprintf('class="%s"', $classText);
                                }
                                continue;
                            }
                            // append styles
                            if ($this->isStyleAttribute($attribute)) {
                                if ($styleText = $node->getStyleText()) {
                                    $attributes[] = sprintf('style="%s"', $styleText);
                                }
                                continue;
                            }
                            $attributes[] = sprintf('%s="%s"', $attribute->name, $attribute->value);
                        }
                        // pass empty class/style count
                        $attributes = count($attributes)
                            ? ' '. join(' ', $attributes)
                            : '';
                    }
                    // check is self closing tag or not
                    if ($node->selfClosing) {
                        $string .= sprintf('<%s%s />', $node->name, $attributes);
                    } else {
                        $string .= sprintf('<%s%s>', $node->name, $attributes);
                        // do recursion if has children
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
     * Check target node ($this) can istert the given node as child.
     *
     * @param  Node $node
     * @throws Dom\Error\Node
     * @return boolean
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

        // wtf the hierarchy rules.. @mustbeextended
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

        // this is trivial but stands for future dev
        return true;
    }

    /**
     * Check attribute whether class or not.
     *
     * @param  Attribute $attribute
     * @return boolean
     */
    protected function isClassAttribute(Attribute $attribute) {
        return ($attribute->name == self::ATTRIBUTE_NAME_CLASS
                    && $attribute->value instanceof ClassCollection);
    }

    /**
     * Check attribute whether style or not.
     *
     * @param  Attribute $attribute
     * @return boolean
     */
    protected function isStyleAttribute(Attribute $attribute) {
        return ($attribute->name == self::ATTRIBUTE_NAME_STYLE
                    && $attribute->value instanceof StyleCollection);
    }
}
