<?php

namespace Gdbots\Tests\QueryParser\Parser;

use Gdbots\QueryParser\Node;
use Gdbots\QueryParser\QueryResult;
use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Visitor\QueryItemPrinter;

class QueryResultTest extends \PHPUnit_Framework_TestCase
{
    /** @var QueryResult */
    protected $result;

    /** @var QueryItemPrinter */
    protected $printer;

    public function setUp()
    {
        $this->result = new QueryResult();
        $this->printer = new QueryItemPrinter();
    }

    public function tearDown()
    {
        $this->result = null;
        $this->printer = null;
    }

    /**
     * @dataProvider getTestParseQueriesDataprovider
     */
    public function testParseResult($string, $print, array $itemCount = [], array $queryItems = [])
    {
        // get array of tokens
        $tokens = $this->result->parse($string);

        // check print output
        $output =  $this->getPrintContent($this->result->getQueryItem());
        $output = preg_replace("/[\r\n]+/", '', $output);
        $output = preg_replace('/\s+/', '', $output);

        $this->assertEquals($print, $output);

        // check total items per token type
        $this->assertEquals(count($itemCount), count($tokens));

        // check single type item count
        foreach ($tokens as $key => $token) {
            $method = 'get'.ucfirst(strtolower($key)).'s';

            $this->assertEquals(count($this->result->$method()), count($token));
        }

        // validate each token type item values
        $allTokenArray = [];
        $tokenTypes = ['WORD', 'PHRASE', 'URL', 'NUMBER', 'DATE', 'HASHTAG', 'MENTION', 'FILTER'];

        foreach ($tokenTypes as $tokenType) {
            $method = 'get'.ucfirst(strtolower($tokenType)).'s';
            $items = $this->result->$method();

            foreach ($items as $item) {
                $tokenArray = [];

                if ($item instanceof Node\AbstractSimpleTerm) {
                    $tokenValue = $item->getToken();
                }

                if ($item instanceof Node\ExplicitTerm) {
                    $tokenField = $item->getNominator()->getToken();
                    $tokenValue = $item->getTerm()->getToken();
                    $tokenTypeText = $item->getTokenTypeText();
                }

                if ($item->getTokenType() === QueryLexer::T_FILTER) {
                    $tokenArray['field'] = $tokenField;
                    $tokenArray['operator'] = $tokenTypeText;
                }

                $tokenArray['value'] = $tokenValue;
                $tokenArray['boost'] = $item->getBoostBy();
                $tokenArray['exclude'] = $item->isExcluded();
                $tokenArray['include'] = $item->isIncluded();

                $allTokenArray[$tokenType][] = $tokenArray;
            }
        }

        $this->assertEquals($queryItems, $allTokenArray);
    }

    public function getTestParseQueriesDataprovider()
    {
        return json_decode(file_get_contents(__DIR__.'/../Fixtures/query-string.json'), true);
    }

    /**
     * @return string
     */
    private function getPrintContent(Node\AbstractQueryItem $query)
    {
        ob_start();

        $query->accept($this->printer);

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
}
