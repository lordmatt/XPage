<p>Testing Starts</p>
    
<?php

include "./XPage/XPage.php";

$page = new XPage('<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>This is the title</title>
</head>
<body>

<header>
<h1>This is the heading</h1>
</header>
<section>
<p>Text</p>
<p>Text</p>
<p>Text</p>
<!-- comment -->
<p>Text</p>
<p>Text</p>
</section>
<footer><p>This is the footer.</p></footer>

</body>
</html>');

echo "<h3>Any errors?</h3><pre>";
print_r($page->flush_error_log());
echo "</pre>";
echo "<h3>Our xHTML5 doc</h3>";
echo "<textarea style='width:99%;height:20em;'>", $page->asHTML() , "</textarea>";

$page->page()->body->header->addChild('p','This is still the header');

/*
 * With bookmarking do we self referernece or pass XPath?
 */

$page->page()->body->header->register_as('bob');
$bob = $page->getZone('bob');
$bob->addChild('p','Adding via Bob');

echo "<h3>Our xHTML5 doc after edits</h3>";
echo "<textarea style='width:99%;height:20em;'>", $page->asHTML() , "</textarea>";