<?php


namespace Gdbots\QueryParser;

// Include the composer autoloader
$loader = require dirname(__DIR__) . '/vendor/autoload.php';

use Gdbots\QueryParser\Parser\QueryParser;
use Gdbots\QueryParser\Visitor\QueryItemPrinter;
use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\Node\Text;
use Gdbots\QueryParser\Node\ExplicitTerm;
use Gdbots\QueryParser\Node\Hashtag;

//$str= 'brian"what the"@mention #hashtag"#hash me" brian^100 +include -exclude';

$str= '+a:>b';




$qs = new QueryParser();

$qs->readString($str, true);
$result = $qs->parse();
print_r($result);
$printer = new QueryItemPrinter();
$result->accept($printer);
/*print_r($result);
print_r($result->getNominator());

echo $result->getNominator()->getToken();

echo $result->getTerm()->getToken();*/
//->getSubExpression()->geToken();

//$printer = new QueryItemPrinter();
//$result->accept($printer);
//print_r($result);
//var_dump($result);

/*$result = $qs->parse();

$e = $qs->getErrors();

print_r($result);*/
