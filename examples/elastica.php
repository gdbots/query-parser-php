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
;

$qs = isset($argv[1]) ? $argv[1] : 'test';
$parsedQuery = $parser->parse($qs);
if (!$parsedQuery->hasAMatchableNode()) {
    echo 'query: '.$qs.PHP_EOL;
    echo 'has no matchable nodes.'.PHP_EOL.PHP_EOL.PHP_EOL;
    exit;
}
$builder->addParsedQuery($parsedQuery);

$options = [Search::OPTION_FROM => 0, Search::OPTION_SIZE => 2];
$query = $builder->getQuery();

$query = (new FunctionScore())
    ->setQuery($query)
    ->setBoostMode(FunctionScore::BOOST_MODE_SUM)
    ->addFunction('field_value_factor', [
        'field' => '__popularity',
        'modifier' => 'none',
    ], null, 0.4);

$query = \Elastica\Query::create($query);
//$query->setSort(['__popularity' => 'desc']);
$results = $client->getIndex($index)->search($query, $options);


echo 'Total Time / Count: ' . $results->getTotalTime() . ' / ' . $results->getTotalHits() . str_repeat(PHP_EOL, 3);

foreach ($results as $result) {
    $src = $result->getSource();
    $targetPublishedAt = isset($src['target_published_at']) ? $src['target_published_at'] : substr($src['target_published_at'], 0, 10);
    $targetPublishedAt = new \DateTime('@'.$targetPublishedAt);
    echo 'score: ' . json_encode($result->getScore()). PHP_EOL;
    echo '_id: ' . $src['_id'] . PHP_EOL;
    echo 'title: ' . $src['title'] . PHP_EOL;
    echo 'target_published_at: ' . $targetPublishedAt->format('Y-m-d') . PHP_EOL;
    echo 'excerpt: ' . $src['excerpt'] . PHP_EOL;
    echo 'seo_keywords: ' . json_encode(isset($src['seo_keywords']) ? $src['seo_keywords'] : []) . PHP_EOL;
    echo 'plays_count: ' . (isset($src['plays_count']) ? $src['plays_count'] : '') . PHP_EOL;
    echo 'hashtags: ' . json_encode(isset($src['hashtags']) ? $src['hashtags'] : []) . PHP_EOL;

    //echo json_encode($src, JSON_PRETTY_PRINT) . PHP_EOL;

    echo str_repeat(PHP_EOL, 3).str_repeat('*', 70).str_repeat(PHP_EOL, 3);
    //fgets(STDIN);
}


