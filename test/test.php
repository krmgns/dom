<?php
header('Content-Type: text/plain; charset=utf-8');

// Simple dump
function pre($input, $exit = false){
    printf("%s\n", print_r($input, true));
    if ($exit) {
        exit;
    }
}
function prd($input, $exit = false){
    var_dump($input);
    if ($exit) {
        exit;
    }
}

spl_autoload_register(function($name) {
    require sprintf('%s/../src/%s.php', __dir__,
        preg_replace(['~^\Dom~', '~\\\~'], ['', '/'], $name));
});

/*****************************/
use Dom\Dom;
use Dom\Node\Document;

// Create first Document node (set as xml)
$dom = new Dom();
$doc = $dom->document(Document::DOCTYPE_XML);
// With more args: "doctype def=xml", "encoding def=utf-8", "version def=1.0"
// $doc = $dom->document(Document::DOCTYPE_XML, "utf-16", "1.1");

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
return;

$dom = new Dom\Dom();
$doc = $dom->document();

$body = $doc->createElement('body');
$body->appendTo($doc);

$div = $doc->createElement('div', [
    'id' => 'theDiv',
    'class' => 'cls1 cls2',
    'style' => ['color' => '#ff0']
], 'The DIV text...');
$div->appendTo($body);

$pre = $doc->createElement('pre', ['class' => 'pre-class'])
           ->append($doc->createElement('br'))
           ->append($doc->createElement('i', null, 'i0'))
           ->append($doc->createElement('i', null, 'i1'));
$pre->appendTo($div);

// prd($pre->isChildOf($div));
// prd($pre->isChildOf($body));
// prd($body->isChildOf($div));
// prd($div->isChildOf($div));
// prd($div->isChildOf($body));
// prd($body->isParentOf($div));


// $div->addClass('cls3');
// $div->removeClass('cls1');
// prd($div->hasClass('cls1'));
// prd($div->hasClass('cls2'));
// prd($div->getClassText());

// $div->setStyle('width', '330px');
// $div->removeStyle('color');
// prd($div->getStyle('color'));
// prd($div->getStyle('width'));

// $div->setAttribute('data-foo', 'The Foo!');
// prd($div->hasAttribute('data-foo'));
// prd($div->getAttribute('data-foo'));
// $div->removeAttribute('data-foo');
// prd($div->hasAttribute('data-foo'));
// prd($div->getAttribute('data-foo'));

// $div->setAttributeObject(new \Dom\Node\Attribute('data-foo', 'The Foo!', $div));
// prd($div->getAttribute('data-foo'));

// $atr = new \Dom\Node\Attribute('data-foo', 'The Foo!');
// $atr->setOwnerElement($div);
// $div->setAttributeObject($atr);
// prd($div->getAttribute('data-foo'));

// pre($pre->item(1)->prev()->getInnerText());
// pre($pre->item(0)->next()->getInnerText());
// pre($pre->item(1)->prevAll()->item(0)->getInnerText());
// pre($pre->item(0)->nextAll()->item(0)->getInnerText());

// $br = $doc->createElement('br');
// $hr = $doc->createElement('hr');
// $div->prepend($br);

// $br->replace($hr);
// $div->replaceChild($br, $hr);

// $br->after($hr);
// $br->before($hr);

// $hr->appendTo($body);
// $hr->prependTo($body);

// $hr->appendAfter($div);
// $hr->appendBefore($div);

// $div->remove($br);

// $div = $div->doClone();
// $div->appendTo($body);

// $div->doEmpty();

// $div->appendText('More text for DIV!');
// $div->appendComment('#comment node');
// $div->appendCData('#cdata node');

// prd($div->hasChildren());   // true
// prd($br->hasChildren());    // false
// prd($div->hasAttributes()); // true
// prd($br->hasAttributes());  // false
// $br->setAttribute('foo', 1);
// prd($br->hasAttributes());  // true
// prd($br->getAttribute('foo')); // 1
// prd($br->hasAttribute('foo')); // true
// $br->removeAttribute('foo');
// prd($br->hasAttribute('foo')); // false

// Trivials
// $el = $doc->createText("\nText contents...\n");
// $body->append($el);
// $el = $doc->createComment("\nComment contents...\n");
// $body->append($el);
// $el = $doc->createCData("\nCData contents...\n");
// $body->append($el);

// $pre->setInnerText('pre..');
// pre($pre->getInnerText());

// $div = $body->item(0);
// pre($div->name);
// pre($pre->first()->name);
// pre($pre->last()->name);
// pre($pre->item(1)->prev()->name);
// pre($pre->last()->prev()->name);
// pre($pre->first()->next()->name);
// pre($pre->first()->nextAll()->length);
// pre($pre->last()->prevAll()->length);
// pre($pre->item(1)->siblings()->length);
// pre($div->toString());

// pre($pre->getPath());
// pre($pre->item(0)->getPath());
// pre($pre->item(1)->getPath());
// pre($pre->item(2)->getPath());

// html output
pre($doc->toString());

pre($doc);
pre('---');
