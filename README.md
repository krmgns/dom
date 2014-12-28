**NOT COMPLETED!**

As a Web Developer, I always loved the [DOM Tree](//en.wikipedia.org/wiki/Document_Object_Model) in my profession and tried to keep it simple to work with it.

So, DOM object creates HTML and XML documents on the fly! It is useful anytime you need to create a DOM tree, especially when working with such as contents in raw codes (e.g AJAX files that returns contents in HTML or XML format). It will prepare a prefect DOM tree and give a clean output without struggling to generate contents in string quotes (").

Now, let's see what we can do it that sweet thing... :)

**Notices**

- It is not 100% implementation of [WC3 DOM specs](//www.w3.org/DOM) (contains inclusions/exclusions), but very similar to [mii.js](//github.com/qeremy/mii)
- It does not use [PHP DOM](//php.net/book.dom)
- "For now", it designed for only create and modify HTML/XML documents
- Set your autoloder first to get it work well
- See all method maps of objects after samples
- See `pre()` and `prd()` functions in `test.php`
- Requires PHP >= 5.3 

**Sample: HTML Documents**

```php
// Create first Document node (default #document)
$dom = new Dom\Dom();
$doc = $dom->document();

// Create <body> node and append into Document node
$body = $doc->createElement('body');
$body->appendTo($doc);

// Create <div> node with "attributes" and "textContent"
$div = $doc->createElement('div', [
    'id' => 'theDiv',
    'class' => 'cls1 cls2',
    'style' => ['color' => '#ff0']
], 'The DIV text...');

// Append <div> into <body>
$div->appendTo($body);
// Or $body->append($div);

// And get Document contents as HTML output
$html = $doc->toString();
pre($html);

// Result
<!DOCTYPE html>
<body><div class="cls1 cls2" style="color:#ff0;" id="theDiv">The DIV text...</div></body>
```

**Sample: XML Documents**

```php
// Create first Document node (set as xml)
$dom = new Dom\Dom();
$doc = $dom->document(Dom\Node\Document::DOCTYPE_XML);
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

// And get Document contents as XML output
$xml = $doc->toString();
pre($xml);

// Result
<?xml version="1.0" encoding="utf-8"?>
<fruits><apples><apple color="yellow" /><apple color="green" /></apples></fruits>
```

**Sample: REST API Page**

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
            // Set inner text
            $messageNode->appendText($message->text); // Or appendCData if needed
            // Append child node into root node
            $messageNode->appendTo($messagesNode);
        }

        // Get output
        $xml = $doc->toString();
        // Send response as XML
        $response->send($xml);
});
```

**Method Map of `Dom\Node\Node`**

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
Node $clone                      $clone.doClone(bool $deep = false)

// Controller methods
bool                             $node.hasChildren(void)
bool                             $node.hasAttributes(void)

// Attribute methods
Node $node                       $node.setAttributeObject(Attribute $attribute)
Attribute $attribute             $node.getAttributeObject(string name)
Node $node                       $node.setAttribute(string $name, mixed $value = null)
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

// Content methods
Node $node                       $node.setInnerText(string $contents)
string                           $node.getInnerText(void)

// Misc. methods
string                           $node.getPath(void)
string                           $node.toString(void)
void                             $node.setParent(Node $parent = null)
void                             $node.setOwnerDocument(Document $ownerDocument = null)
bool                             $node.isChildOf(Node $target)
bool                             $node.isParentOf(Node $target)
bool                             $node.isSameNode(Node $target)
bool                             $node.isSelfClosing(void)
```


