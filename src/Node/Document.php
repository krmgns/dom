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
 * @object  Dom\Node\Document
 * @extends Dom\Node
 * @uses    Dom\Error
 * @version 1.0
 * @author  Kerem Gunes <k-gun@mail.com>
 */
class Document
    extends Node
{
    /**
     * Default doctypes.
     * @const string
     */
    const DOCTYPE_XML      = 'xml',
          DOCTYPE_HTML     = 'html';

    /**
     * Default encoding/version.
     * @const string
     */
    const DEFAULT_ENCODING = 'utf-8',
          DEFAULT_VERSION  = '1.0';


    /**
     * Document type (xml/html).
     * @var Dom\Node\DocumentType
     */
    protected $doctype;

    /**
     * Charset encoding (used in only xml documents).
     * @var string
     */
    protected $encoding;

    /**
     * XML version (used in only xml documents).
     * @var string
     */
    protected $version;

    /**
     * Create a new Document object.
     *
     * @param string $doctype
     * @param string $encoding
     * @param string $version
     */
    public function __construct(
        $doctype  = self::DOCTYPE_HTML,
        $encoding = self::DEFAULT_ENCODING,
        $version  = null
    ) {
        // call parent init
        parent::__construct('#document', null, Node::TYPE_DOCUMENT);

        // set doctype
        $this->doctype = new DocumentType($doctype);
        $this->doctype->setOwnerDocument($this);

        // set encoding/version
        if ($doctype == self::DOCTYPE_XML) {
            $this->encoding = $encoding ? $encoding : self::DEFAULT_ENCODING;
            $this->version  = $version  ? $version  : self::DEFAULT_VERSION;
        }
    }

    /**
     * Proxy for self.doctype.addDoctypeString().
     *
     * @param boolean $option
     */
    public function addDoctypeString($option) {
        $this->doctype->addDoctypeString($option);
    }

    /**
     * Create a new Element node.
     *
     * @param  string     $tag         (tagName)
     * @param  array|null $attributes
     * @param  string     $text        (innerText aka textContent)
     * @param  boolean    $selfClosing (used for manual self closing option for xml nodes)
     * @return Element
     */
    public function createElement($tag, array $attributes = null, $text = '', $selfClosing = null) {
        $object = new Element($tag, null, $attributes, $selfClosing);
        // Set owner document
        $object->setOwnerDocument($this);
        // Shortcut for appending text nodes
        if ($text) {
            $object->appendText($text);
        }

        return $object;
    }

    /**
     * Create text/cdata/comment node only.
     *
     * Notice: Element node not allowed, use self.createElement().
     *
     * @param  string $type     (nodeName)
     * @param  string $contents (nodeValue)
     * @throws Error\Node
     * @return Node
     */
    public function create($type, $contents) {
        switch ($type) {
            case self::TYPE_ELEMENT:
                throw new Error\Node(
                    'Not implemented! Use Document::createElement instead.');
            case self::TYPE_TEXT:
                return $this->createText($contents);
            case self::TYPE_CDATA:
                return $this->createCData($contents);
            case self::TYPE_COMMENT:
                return $this->createComment($contents);
        }

        throw new Error\Node('Unknown node type!');
    }

    /**
     * Create a new Text node.
     *
     * @param  string $contents
     * @return Text
     */
    public function createText($contents) {
        $object = new Text($contents);
        $object->setOwnerDocument($this);

        return $object;
    }

    /**
     * Create a new CData node.
     *
     * @param  string $contents
     * @return CData
     */
    public function createCData($contents) {
        $object = new CData($contents);
        $object->setOwnerDocument($this);

        return $object;
    }

    /**
     * Create a new Comment node.
     *
     * @param  string $contents
     * @return Comment
     */
    public function createComment($contents) {
        $object = new Comment($contents);
        $object->setOwnerDocument($this);

        return $object;
    }

    // @notimplemented
    public function getElementById($id) {}
    public function getElementsByTagName($tagName) {}
    public function getElementsByClassName($className) {}

    // @notimplemented
    public function load($html) {}
    public function save() {}
}
