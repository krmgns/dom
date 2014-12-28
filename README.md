**NOT COMPLETED!**

**First**

- It is not an exact implementation of WC3 DOM specs, but similar to mii.js (see: /qeremy/mii)
- It does not use PHP DOM (php.net/book.dom)
- "For now", it designed for only create and modify HTML/XML documents
- Set your autoloder first to get it work well
- See `pre()` and `prd()` functions in `test.php`
- Requires PHP >= 5.3 

** HTML Documents

```php
// Create first #document node
$dom = new Dom\Dom();
$doc = $dom->document();

// Create <body>
$body = $doc->createElement('body');

// Add some child nodes into <body>
$div = $doc->createElement('div', [
    'id' => 'theDiv',
    'class' => 'cls1 cls2',
    'style' => ['color' => '#ff0']
], 'The DIV text...');

// Node.appendChild
$body->append($div);
// Or $div->appendTo($body);

// And get #document contents as HTML output
$html = $doc->toString();
pre($html);

// Gives
<!DOCTYPE html>
<body><div class="cls1 cls2" style="color:#ff0;" id="theDiv">The DIV text...</div></body>
```

** XML Documents

```php
$dom = new Dom\Dom();
$doc = $dom->document(Dom\Node\Document::DOCTYPE_XML);

$fruits = $doc->createElement('fruits');
$apples = $doc->createElement('apples')
    ->append($doc->createElement('apple', ['color' => 'yellow'], '', true))
    ->append($doc->createElement('apple', ['color' => 'green'], '', true))
    ->appendTo($fruits);

$fruits->appendTo($doc);

$xml = $doc->toString();
pre($xml);

// Gives
<?xml version="1.0" encoding="utf-8"?>
<fruits><apples><apple color="yellow" /><apple color="green" /></apples></fruits>
```

** `Dom\Node\Node` method map

```php
// Modifier methods
Node $parent    $parent.append(Node $child)
Node $parent    $parent.prepend(Node $child)
Node $new       $old.replace(Node $new)
Node $parent    $parent.replaceChild(Node $new, Node $new)
Node $target    $target.before(Node $sibling)
Node $target    $target.after(Node $sibling)
Node $child     $child.appendTo(Node $parent)
Node $child     $child.prependTo(Node $parent)
Node $new       $new.appendAfter(Node $target)
Node $new       $new.appendBefore(Node $target)
Node $parent    $parent.remove(Node $child)
Node $node      $node.doEmpty(void)
Node $node      $node.appendText(string $contents)
Node $node      $node.appendComment(string $contents)
Node $node      $node.appendCData(string $contents)
```


