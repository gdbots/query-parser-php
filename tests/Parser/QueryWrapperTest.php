<?php

namespace Gdbots\Tests\QueryParser\Parser;

use Gdbots\QueryParser\Node;
use Gdbots\QueryParser\QueryWrapper;
use Gdbots\QueryParser\QueryScanner;
use Gdbots\QueryParser\Visitor\QueryItemPrinter;

class QueryWrapperTest extends \PHPUnit_Framework_TestCase
{
    /** QueryWrapper */
    protected $wrapper;

    /** QueryItemPrinter */
    protected $printer;

    public function setUp()
    {
        $this->wrapper = new QueryWrapper();
        $this->printer = new QueryItemPrinter();
    }

    public function tearDown()
    {
        $this->wrapper = null;
        $this->printer = null;
    }

    /**
     * @dataProvider getTestParseQueriesDataprovider
     */
    public function testWrapperParse($string, $print, array $itemCount = [], array $queryItems = [])
    {
        $this->wrapper->parse($string);
        $query = $this->wrapper->getParseResultQueryItem();

        // check print output
        $output =  $this->getPrintContent($query);
        $output = preg_replace("/[\r\n]+/", '', $output);
        $output = preg_replace('/\s+/', '', $output);

        $this->assertEquals($print, $output);

        // get array of tokens
        $tokens = $query->getQueryItemsByTokenType();

        // check total items per token type
        $this->assertEquals(count($itemCount), count($tokens));

        // check single type item count
        foreach ($tokens as $key => $token) {
            $method = 'get'.ucfirst(strtolower($key)).'s';

            $this->assertEquals(1+count($this->wrapper->$method()), count($token));
        }

        // validate each token type item values
        $allTokenArray = [];
        $tokenTypes = ['FILTER', 'HASHTAG', 'MENTION', 'PHRASE', 'URL', 'WORD'];

        foreach ($tokenTypes as $tokenType) {
            $items = $query->getQueryItemsByTokenType(constant('Gdbots\QueryParser\QueryScanner::T_'.$tokenType));

            foreach ($items as $item) {
                $tokenArray = [];

                if ($item instanceof Node\SimpleTerm) {
                    $tokenValue = $item->getToken();
                } else {
                    if ($item->getTokenType() === QueryScanner::T_FILTER) {
                        $tokenField = $item->getNominator()->getToken();
                        $tokenValue = $item->getTerm()->getToken();
                        $tokenTypeText = $item->getTokenTypeText();
                    } else {
                        $tokenValue = $item->getExpression()->getToken();
                    }
                }

                $boosted = $item->getBoostBy();
                $excluded = $item->isExcluded();
                $included = $item->isIncluded();

                if ($item->getTokenType() === QueryScanner::T_FILTER) {
                    $tokenArray['field'] = $tokenField;
                    $tokenArray['operator'] = $tokenTypeText;

                }
                $tokenArray['value'] = $tokenValue;

                if ($boosted) {
                    $tokenArray['boost'] = $boosted;
                }
                if ($excluded) {
                    $tokenArray['exclude'] = true;
                }
                if ($included) {
                    $tokenArray['include'] = true;
                }

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
    private function getPrintContent(Node\QueryItem $query)
    {
        ob_start();

        $query->accept($this->printer);

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
}
