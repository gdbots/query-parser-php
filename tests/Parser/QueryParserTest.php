<?php

namespace Gdbots\Tests\QueryParser\Parser;

use Gdbots\QueryParser\Node;
use Gdbots\QueryParser\QueryParser;
use Gdbots\QueryParser\QueryScanner;
use Gdbots\QueryParser\Visitor\QueryItemPrinter;

class QueryParserTest extends \PHPUnit_Framework_TestCase
{
    /** QueryParser */
    protected $parser;

    /** QueryItemPrinter */
    protected $printer;

    public function setUp()
    {
        $this->parser = new QueryParser();
        $this->printer = new QueryItemPrinter();
    }

    public function tearDown()
    {
        $this->parser = null;
        $this->printer = null;
    }

    /**
     * @dataProvider getTestParseWithOneClassDataprovider
     */
    public function testParseNode($string, $class)
    {
        $this->parser->readString($string);
        $query = $this->parser->parse();

        $this->assertInstanceOf($class, $query);
    }

    public function getTestParseWithOneClassDataprovider()
    {
        return [
            ['phrase', 'Gdbots\QueryParser\Node\Word'],
            ['"phrase"', 'Gdbots\QueryParser\Node\Phrase'],
            ['country:"United State"', 'Gdbots\QueryParser\Node\ExplicitTerm'],
            ['phrase^123', 'Gdbots\QueryParser\Node\Word'],
            ['-phrase', 'Gdbots\QueryParser\Node\Word'],
            ['+phrase', 'Gdbots\QueryParser\Node\Word'],
            ['#phrase', 'Gdbots\QueryParser\Node\Hashtag'],
            ['@phrase', 'Gdbots\QueryParser\Node\Mention'],
            ['phrase word', 'Gdbots\QueryParser\Node\OrExpressionList'],
            ['phrase OR word', 'Gdbots\QueryParser\Node\OrExpressionList'],
            ['phrase AND word', 'Gdbots\QueryParser\Node\AndExpressionList'],
            ['(phrase OR word)', 'Gdbots\QueryParser\Node\Subexpression']
        ];
    }

    /**
     * @dataProvider getTestParseQueriesDataprovider
     */
    public function testParseQuery($string, $print, array $itemCount = [], array $queryItems = [])
    {
        $this->parser->readString($string, true);
        $query = $this->parser->parse();

        // check print output
        $output =  $this->getPrintContent($query);
        $output = preg_replace("/[\r\n]+/", '', $output);
        $output = preg_replace('/\s+/', '', $output);

        $this->assertEquals($print, $output);

        // check total items per token type
        $tokens = $query->getQueryItemsByTokenType();

        $totalCount = 0;
        foreach ($tokens as $tokenBuckets) {
            $totalCount += count($tokenBuckets);
        }

        $runningCount = 0;
        foreach ($itemCount as $key => $value) {
            $runningCount += $value;

            $this->assertArrayHasKey($key, $tokens);
            $this->assertEquals($value, count($tokens[$key]));

            // check single type item count
            $items = $query->getQueryItemsByTokenType(constant('Gdbots\QueryParser\QueryScanner::T_'.$key));
            $this->assertEquals($value, count($items));
        }

        $this->assertEquals($totalCount, $runningCount);

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

    public function testParseTextWithUnclosedQuotes()
    {
        $this->parser->readString('"phrase');
        $query = $this->parser->parse();

        $this->assertInstanceOf('Gdbots\QueryParser\Node\Word', $query);
        $this->assertEquals('phrase', $query->getToken());
        $this->assertEquals(QueryScanner::T_WORD, $query->getTokenType());
    }

    public function testParseMultiHashtags()
    {
        $this->parser->readString('#one #two #three');
        $query = $this->parser->parse();

        $output = " Or
> Hashtag: one
> Hashtag: two
> Hashtag: three
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseDuplicateHashtags()
    {
        $this->parser->readString('##phrase');
        $query = $this->parser->parse();

        $output = " Hashtag: phrase
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseFilterWithBoost()
    {
        $this->parser->readString('table.fieldName:value^123');
        $query = $this->parser->parse();

        $output = " Term: table.fieldName : value ^ 123.00
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQuery()
    {
        $this->parser->readString('(("phrase" #phrase) table.fieldName:value)^123');
        $query = $this->parser->parse();

        $output = " Subexpression
> Or
>> Subexpression
>>> Or
>>>> Phrase: phrase
>>>> Hashtag: phrase
>> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQueryUsingOperator()
    {
        $this->parser->readString('(("phrase" OR #phrase) AND table.fieldName:value)^123');
        $query = $this->parser->parse();

        $output = " Subexpression
> And
>> Subexpression
>>> Or
>>>> Phrase: phrase
>>>> Hashtag: phrase
>> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQueryWithIgnoreOperator()
    {
        $this->parser->readString('(("phrase" OR #phrase) AND table.fieldName:value)^123', true);
        $query = $this->parser->parse();

        $output = " Or
> Phrase: phrase
> Hashtag: phrase
> Term: table.fieldName : value ^ 123.00
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseEmoji()
    {
        $this->parser->readString('#emoji 💩 AND 🍦 OR 😳');
        $query = $this->parser->parse();

        $output = " And
> Or
>> Hashtag: emoji
>> Phrase: &#x1f4a9;
> Or
>> Phrase: &#x1f366;
>> Phrase: &#x1f633;
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseGetHashtagQueryItems()
    {
        $this->parser->readString('(("phrase" OR #phrase) AND table.fieldName:value) #boost');
        $query = $this->parser->parse();

        $hasttags = $query->getQueryItemsByTokenType(\Gdbots\QueryParser\QueryScanner::T_HASHTAG);

        $this->assertEquals(2, count($hasttags));
        $this->assertEquals('phrase', $hasttags[0]->getToken());
        $this->assertEquals('boost', $hasttags[1]->getToken());
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
