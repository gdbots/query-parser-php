<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Gdbots\QueryParser\QueryResult;
use Gdbots\QueryParser\Visitor\QueryItemPrinter;

$result = new QueryResult();

$tests = [
    //'http://test.com/#test omg#lol',
    'test (a b) +field:>1'
    /*
    'test || AND what (+test)',
    'outer:(abc inner:123)',
    '#cats #dogs',
    'target_curie:vevo:video',
    '@user outer:abc AND (+test +inner:123)',
    'Beyoncé Giselle Knowles-Carter ',
    'Beyoncé Knowles (@Beyonce) | Twitter',
    'Beyoncé Knowles (@Beyonce) <a> Twitter',
    */
];

$printer = new QueryItemPrinter();

foreach ($tests as $test) {
    $result->parse($test);
    echo 'input: ' . $test . PHP_EOL;
    $result->getQueryItem()->accept($printer);
    echo 'compiled: ' . $result->getCompiledString() . PHP_EOL;
    echo 'hashtags: ' . json_encode($result->getHashtags()) . PHP_EOL;
    echo 'filters: ' . json_encode($result->getFilters()) . PHP_EOL;
    echo 'words: ' . json_encode($result->getWords()) . PHP_EOL;
    echo str_repeat('=', 10) . PHP_EOL . PHP_EOL;
    fgets(STDIN);


    $result->parse(html_entity_decode(htmlentities($test)));
    echo 'input: ' . $test . PHP_EOL;
    $result->getQueryItem()->accept($printer);
    echo 'compiled: ' . $result->getCompiledString() . PHP_EOL;
    echo 'hashtags: ' . json_encode($result->getHashtags()) . PHP_EOL;
    echo 'filters: ' . json_encode($result->getFilters()) . PHP_EOL;
    echo 'words: ' . json_encode($result->getWords()) . PHP_EOL;
    echo str_repeat('=', 10) . PHP_EOL . PHP_EOL;
    fgets(STDIN);


    $result->parse(htmlentities($test));
    echo 'input: ' . $test . PHP_EOL;
    $result->getQueryItem()->accept($printer);
    echo 'compiled: ' . $result->getCompiledString() . PHP_EOL;
    echo 'hashtags: ' . json_encode($result->getHashtags()) . PHP_EOL;
    echo 'filters: ' . json_encode($result->getFilters()) . PHP_EOL;
    echo 'words: ' . json_encode($result->getWords()) . PHP_EOL;
    echo str_repeat('=', 10) . PHP_EOL . PHP_EOL;
    fgets(STDIN);
}
