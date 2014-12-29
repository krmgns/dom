As a Web Developer, I always loved the [DOM Tree](//en.wikipedia.org/wiki/Document_Object_Model) in my profession and tried to keep it simple and easy to work with it.

So, DOM object creates XML/HTML documents on the fly! It is useful anytime you need to create a DOM tree, especially when working with such as contents in raw codes (e.g. REST endpoints or AJAX pages that returns contents in XML/HTML format). It will prepare a prefect DOM tree and give a clean output without struggling to generate contents in string quotes (").

Now, let's see what we can do with that sweet thing... :)

**Notices**

- It is not 100% implementation of [WC3 DOM specs](//www.w3.org/DOM) (contains inclusions/exclusions), but very similar to [mii.js](//github.com/qeremy/mii)
- It does not use [PHP DOM](//php.net/book.dom) Library
- "JUST FOR NOW", it designed for only create and modify XML/HTML documents (will be extended for Parse/Query supports)
- Set your autoloder first to get it work well
- See all method maps of objects after samples
- See `pre()` and `prd()` functions in `test.php`
- Requires PHP >= 5.3 (only samples contain 5.4 features, e.g `[]` arrays)


**Copying**

```bash
# clone dom
$ cd /var/www/<PROJECTPATH>
$ git clone git@github.com:qeremy/dom.git && cd dom

# install composer
$ curl -sS https://getcomposer.org/installer | php
composer.phar update
```

**Autoload**

```php
$loader = require('vendor/autoload.php');
$loader->add('Dom\\', __dir__.'/dom');
```

**Sample: HTML Documents**

```php
// Create first Document node (default #document)
$dom = new \Dom\Dom();
$doc = $dom->document();

// Create <body> node and append into Document node
$body = $doc->createElement('body');
$body->appendTo($doc);

// Create <div> node with "attributes" and "textContent"
$div = $doc->createElement('div', [
    'id'    => 'foo',
    'class' => 'cls1 cls2',
    'style' => ['color' => '#ff0']
], 'The DIV text...');

// Append <div> into <body>
$div->appendTo($body);
// Or $body->append($div);

// Finally get Document contents as HTML output
$html = $doc->toString();
pre($html);

// Result
<!DOCTYPE html>
<body><div class="cls1 cls2" style="color:#ff0;" id="theDiv">The DIV text...</div></body>
```

**Sample: XML Documents**

```php
// Create first Document node (set as xml)
$dom = new \Dom\Dom();
$doc = $dom->document(\Dom\Node\Document::DOCTYPE_XML);
// With more args: "doctype def=xml", "encoding def=utf-8", "version def=1.0"
// $doc = $dom->document(Dom\Node\Document::DOCTYPE_XML, "utf-16", "1.1");

// Create <fruits> node and into Document node
$fruits = $doc->createElement('fruits');
$fruits->appendTo($doc);

// Create <fruit> nodes and append into <fruits> node
$apples = $doc->createElement('apples')
    // args: "nodeName", "attributes", "nodeValue", "selfClosing?"
    ->append($doc->createElement('apple', ['color' => 'yellow'], null, true))
    ->append($doc->createElement('apple', ['color' => 'green'],  null, true))
    ->appendTo($fruits);

// Finally get Document contents as XML output
$xml = $doc->toString();
pre($xml);

// Result
<?xml version="1.0" encoding="utf-8"?>
<fruits><apples><apple color="yellow" /><apple color="green" /></apples></fruits>
```

**Sample: REST Page (as an idea)**

```php
// Get user messages
$app->get('/user/:id/messages', ['id' => 123], function($request, $response) use($app) {
    // Built DOM Tree
    $dom = new \Dom\Dom();
    $doc = $dom->document(\Dom\Node\Document::DOCTYPE_XML);

    // Create root node and append it into Document
    $messagesNode = $doc->createElement('messages');
    $messagesNode->appendTo($doc);

    foreach ($app->getUserMessages($request->id) as $message) {
        // Create child node
        $messageNode = $doc->createElement('message', [
            // Add attributes
            'date' => $message->date,
            'read' => $message->read
        ]);
        // Set inner text (or appendCData if needed)
        $messageNode->appendText($message->text);
        // Append child node into root node
        $messageNode->appendTo($messagesNode);
    }

    // Get output
    $xml = $doc->toString();
    // Send response as XML
    $response->send($xml);
});
```

**Node attributes**

```php
// simple
$div->setAttribute('data-foo', 'The Foo!');
prd($div->hasAttribute('data-foo'));   // bool(true)
prd($div->getAttribute('data-foo'));   // string(8) "The Foo!"
$div->removeAttribute('data-foo');
prd($div->hasAttribute('data-foo'));   // bool(false)
prd($div->getAttribute('data-foo'));   // NULL

// set object
$div->setAttributeObject(new \Dom\Node\Attribute('data-foo', 'The Foo!', $div));

// or
$atr = new \Dom\Node\Attribute('data-foo', 'The Foo!');
$atr->setOwnerElement($div);
$div->setAttributeObject($atr);

prd($div->getAttribute('data-foo'));   // string(8) "The Foo!"
```

**Element style/class**

```php
// Add, remove and get style text
$div->setStyle('width', '330px');
$div->removeStyle('color');
prd($div->getStyle('color'));  // NULL
prd($div->getStyle('width'));  // string(5) "330px"

// Add, remove, check and get class text
$div->addClass('cls3');
$div->removeClass('cls1');
prd($div->hasClass('cls1'));   // bool(false)
prd($div->hasClass('cls2'));   // bool(true)
prd($div->getClassText());     // string(9) "cls2 cls3"
```

**Walker Methods**

```php
// Append more child into <div>
$pre = $doc->createElement('pre', ['class' => 'pre-class'])
   ->append($doc->createElement('br'))
   ->append($doc->createElement('i', null, 'i0'))
   ->append($doc->createElement('i', null, 'i1'));
$pre->appendTo($div);

// Get path
pre($pre->getPath());                     // #document/body/div/pre
pre($pre->item(0)->getPath());            // #document/body/div/pre/i[0]
pre($pre->item(1)->getPath());            // #document/body/div/pre/i[1]

// Get first/last child and prev/next sibling(s)
pre($div->first()->name);                 // #text
pre($div->last()->name);                  // pre
pre($pre->first()->name);                 // br
pre($pre->last()->name);                  // i
pre($pre->item(1)->prev()->name);         // br
pre($pre->last()->prev()->name);          // i
pre($pre->first()->next()->name);         // i
pre($pre->first()->nextAll()->length);    // 2
pre($pre->last()->prevAll()->length);     // 2
pre($pre->item(1)->siblings()->length);   // 2
```

**Method Maps**

** `\Dom\Dom`

```php
Document $document $dom.document(
    $doctype=Document.DOCTYPE_HTML, $encoding=Document.DEFAULT_ENCODING, $version=null)
```

** `\Dom\Node\Node`

```php
// Modifier methods
Node $parent                     $parent.append(Node $child)
Node $parent                     $parent.prepend(Node $child)
Node $new                        $old.replace(Node $new)
Node $parent                     $parent.replaceChild(Node $new, Node $new)
Node $target                     $target.before(Node $sibling)
Node $target                     $target.after(Node $sibling)
Node $child                      $child.appendTo(Node $parent)
Node $child                      $child.prependTo(Node $parent)
Node $new                        $new.appendAfter(Node $target)
Node $new                        $new.appendBefore(Node $target)
Node $parent                     $parent.remove(Node $child)
Node $node                       $node.doEmpty(void)
Node $node                       $node.appendText(string $contents)
Node $node                       $node.appendComment(string $contents)
Node $node                       $node.appendCData(string $contents)

// Clone method
Node $clone                      $clone.doClone(bool $deep=false)

// Controller methods
bool                             $node.hasChildren(void)
bool                             $node.hasAttributes(void)

// Attribute methods
Node $node                       $node.setAttributeObject(Attribute $attribute)
Attribute $attribute             $node.getAttributeObject(string name)
Node $node                       $node.setAttribute(string|array $name, mixed $value=null)
bool                             $node.hasAttribute(string $name)
string                           $node.getAttribute(string $name)
Node $node                       $node.removeAttribute(string $name)

// Walker methods
Node $child                      $node.item(int $i)
Node $child                      $node.first(void)
Node $child                      $node.last(void)
Node $child                      $node.last(void)
Node $sibling                    $node.prev(void)
Node $sibling                    $node.next(void)
NodeCollection $nodeCollection   $node.prevAll(void)
NodeCollection $nodeCollection   $node.nextAll(void)
NodeCollection $nodeCollection   $node.siblings(void)
// @notimplemented
NodeCollection $nodeCollection   $node.find(string $selector)

// Content methods
Node $node                       $node.setInnerText(string $contents)
string                           $node.getInnerText(void)

// Misc. methods
string                           $node.getPath(void)
string                           $node.toString(void)
void                             $node.setParent(Node $parent=null)
void                             $node.setOwnerDocument(Document $ownerDocument=null)
bool                             $node.isChildOf(Node $target)
bool                             $node.isParentOf(Node $target)
bool                             $node.isSameNode(Node $target)
bool                             $node.isSelfClosing(void)
```

** `\Dom\Node\DocumentType` extends `\Dom\Node\Node`

```php
void                             $doctype.addDoctypeString(bool)
```

** `\Dom\Node\Document` extends `\Dom\Node\Node`

```php
void                             $document.addDoctypeString(bool) // proxy: $document.doctype.addDoctypeString()
Element $element                 $document.createElement(string $tag, array $attributes=null, $text='', $selfClosing=null) {
Node $node                       $document.create(\Dom\Node\Node.TYPE_(TEXT|CDATA|COMMENT) $type, string $contents)
Node $text                       $document.createText(string $contents)
Node $cData                      $document.createCData(string $contents)
Node $comment                    $document.createComment(string $contents)
// @notimplemented
void                             $document.getElementById(string $id)
void                             $document.getElementsByTagName(string $tagName)
void                             $document.getElementsByClassName(string $className)
// @notimplemented
void                             load(string $html)
void                             save(void)
```

** `\Dom\Node\Element` extends `\Dom\Node\Node`

```php
// Class methods
bool                             $element.hasClass(string $name)
Element $element                 $element.addClass(string|array $value)
Element $element                 $element.removeClass(string $name)
string                           $element.getClassText(void)
ClassCollection $classCollection $element.getClassCollection(void)

// Style methods
Element $element                 $element.setStyle(string|array $name, $value=null)
string                           $element.getStyle($name)
Element $element                 $element.removeStyle(string $name)
string                           $element.getStyleText(void)
StyleCollection $styleCollection $element.getStyleCollection(void)
```

** `\Dom\Patterns\InterfaceTrivialNode`

```php
void                             $sub.setContent(string $content)
string                           $sub.getContent(void)
```

** `\Dom\Node\Text` extends `\Dom\Node\Node` implements `\Dom\Patterns\InterfaceTrivialNode`<br>
** `\Dom\Node\CData` extends `\Dom\Node\Node` implements `\Dom\Patterns\InterfaceTrivialNode`<br>
** `\Dom\Node\Comment` extends `\Dom\Node\Node` implements `\Dom\Patterns\InterfaceTrivialNode`

** `\Dom\Patterns\AbstractElementProperty`

```php
throw                            __set(string $name, string $value)
string $name|$value|throw        __get(string $name)
void                             $sub.setOwnerElement(Element $ownerElement=null)
Element $ownerElement            $sub.getOwnerElement(void)
string                           $sub.getName(void)
string                           $sub.getValue(void)
abstract string                  toString(void)
```

** `\Dom\Node\Style` extends `\Dom\Patterns\AbstractElementProperty`

** `\Dom\Node\Attribute` extends `\Dom\Patterns\AbstractElementProperty`

```php
bool                             $sub.isId(void)
```

** `\Dom\Node\NodeCollection` extends `\Dom\Collection`<br>
** `\Dom\Node\StyleCollection` extends `\Dom\Collection`<br>
** `\Dom\Node\ClassCollection` extends `\Dom\Collection`<br>
** `\Dom\Node\AttributeCollection` extends `\Dom\Collection`

** `\Dom\Collection` implements `\Countable, \IteratorAggregate, \ArrayAccess`

```php
Collection $collection           __construct(array $items=null)
throw                            __set(string $name, mixed $value)
mixed $value|throw               __get(string $name)
Collection $collection           $sub.add(mixed $item)
Collection $collection           $sub.del(int $i)
Collection $collection           $sub.delAll(void)
mixed                            $sub.pop(void)
mixed                            $sub.shift(void)
Collection $collection           $sub.put(int $i, mixed $item)
Collection $collection           $sub.append(mixed $item)
Collection $collection           $sub.prepend(mixed $item)
Collection $collection           $sub.unique(void)
Collection $collection           $sub.filter(\Closure $callback)
Collection $collection|throw     $sub.replace(int $i, mixed $item)
int|null                         $sub.index(mixed $item)
bool                             $sub.has(int $i)
mixed $item|throw                $sub.item(int $i)
array                            $sub.toArray(void)
// \Countable
int                              $sub.count(void)
// \IteratorAggregate
\ArrayIterator $arrayIterator    $sub.getIterator(void)
// \ArrayAccess
Collection $collection|throw     $sub.offsetSet(int $i, mixed $value)
mixed|throw                      $sub.offsetGet(int $i)
Collection $collection           $sub.offsetUnset(int $i)
bool                             $sub.offsetExists($i)
```
