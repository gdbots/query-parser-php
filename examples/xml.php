<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Gdbots\QueryParser\QueryParser;
use Gdbots\QueryParser\Builder\XmlQueryBuilder;

$tests = require __DIR__.'/../tests/Fixtures/test-queries.php';

$parser = new QueryParser();
$builder = new XmlQueryBuilder();
$builder
    ->setEmoticonFieldName('emoticons')
    ->setHashtagFieldName('tags')
    ->setMentionFieldName('mentions')
;

$header = str_repeat(PHP_EOL, 4).'#### %s'.PHP_EOL;

foreach ($tests as $test) {
    $result = $parser->parse($test['input']);

    echo sprintf($header, 'START TEST: '.$test['name']);
    echo $test['input'];


    echo sprintf($header, 'RAW NODES AS JSON');
    echo json_encode($result, JSON_PRETTY_PRINT);


    echo sprintf($header, 'NODES AS XML');
    $xml = $builder->clear()->addParsedQuery($result)->toXmlString();
    echo $xml;


    echo str_repeat(PHP_EOL, 10).str_repeat('*', 70).str_repeat(PHP_EOL, 5);
    fgets(STDIN);
}
