<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Gdbots\QueryParser\QueryParser;
use Gdbots\QueryParser\Builder\ElasticaQueryBuilder;
use Elastica\Client;
use Elastica\Query\FunctionScore;
use Elastica\Search;

class EchoLogger implements \Psr\Log\LoggerInterface
{
    use \Psr\Log\LoggerTrait;

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        echo $message.PHP_EOL;
        echo json_encode($context, JSON_PRETTY_PRINT).PHP_EOL.PHP_EOL;
        echo str_repeat(PHP_EOL, 2).str_repeat('*', 70).str_repeat(PHP_EOL, 2);
    }
}

$host   = getenv('ELASTICA_HOST') ?: '127.0.0.1';
$port   = getenv('ELASTICA_PORT') ?: 9200;
$index  = getenv('ELASTICA_INDEX') ?: 'test';
$client = new Client(['servers' => [['host' => $host, 'port' => $port]]]);
$client->setLogger(new EchoLogger());

$parser  = new QueryParser();
$builder = (new ElasticaQueryBuilder())
    ->setDefaultFieldName('_all')
    ->setEmoticonFieldName('emoticons')
    ->setHashtagFieldName('hashtags')
    ->setMentionFieldName('mentions')
    ->setLocalTimeZone(new DateTimeZone('America/Los_Angeles'))
;

$qs = isset($argv[1]) ? $argv[1] : 'test';
$parsedQuery = $parser->parse($qs);
if (!$parsedQuery->hasAMatchableNode()) {
    echo 'query: '.$qs.PHP_EOL;
    echo 'has no matchable nodes.'.str_repeat(PHP_EOL, 3);
    exit;
}
$builder->addParsedQuery($parsedQuery);

$options = [Search::OPTION_FROM => 0, Search::OPTION_SIZE => 5];
$query = $builder->getBoolQuery();
/*
$query = (new FunctionScore())
    ->setQuery($query)
    ->setBoostMode(FunctionScore::BOOST_MODE_SUM)
    ->addFunction('field_value_factor', [
        'field' => '__popularity',
        'modifier' => 'none',
    ], null, 0.4);
*/
$query = \Elastica\Query::create($query);
//$query->setExplain(true);
//$query->setSort(['published_at' => 'desc']);
$results = $client->getIndex($index)->search($query, $options);

echo 'Total Time (ms) / Records Found:' . PHP_EOL;
echo $results->getTotalTime() . 'ms / ' . $results->getTotalHits() . ' records' . str_repeat(PHP_EOL, 3);
//echo json_encode($results->getResponse()->getData(), JSON_PRETTY_PRINT);

foreach ($results as $result) {
    fgets(STDIN);
    echo json_encode($result->getSource(), JSON_PRETTY_PRINT) . PHP_EOL;
    echo str_repeat(PHP_EOL, 3).str_repeat('*', 70).str_repeat(PHP_EOL, 3);
}
