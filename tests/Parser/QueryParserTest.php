<?php

namespace Gdbots\Tests\QueryParser\Parser;

use Gdbots\QueryParser\Node;
use Gdbots\QueryParser\QueryParser;
use Gdbots\QueryParser\QueryLexer;
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
        $query = $this->parser->parse($string);

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
        $query = $this->parser->parse($string, true);

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
            $items = $query->getQueryItemsByTokenType(constant('Gdbots\QueryParser\QueryLexer::T_'.$key));
            $this->assertEquals($value, count($items));
        }

        $this->assertEquals($totalCount, $runningCount);

        // validate each token type item values
        $allTokenArray = [];
        $tokenTypes = ['WORD', 'PHRASE', 'URL', 'NUMBER', 'DATE', 'HASHTAG', 'MENTION', 'FILTER'];

        foreach ($tokenTypes as $tokenType) {
            $items = $query->getQueryItemsByTokenType(constant('Gdbots\QueryParser\QueryLexer::T_'.$tokenType));

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

    public function testParseComplexQuery()
    {
        $query = $this->parser->parse('(("phrase" #phrase) table.fieldName:value)^123');

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
        $query = $this->parser->parse('(("phrase" OR #phrase) AND table.fieldName:value)^123');

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
        $query = $this->parser->parse('(("phrase" OR #phrase) AND table.fieldName:value)^123', true);

        $output = " Or
> Phrase: phrase
> Hashtag: phrase
> Term: table.fieldName : value ^ 123.00
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseEmoji()
    {
        $query = $this->parser->parse('#emoji 💩 AND 🍦 OR 😳');

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
        $query = $this->parser->parse('(("phrase" OR #phrase) AND table.fieldName:value) #boost');

        $hasttags = $query->getQueryItemsByTokenType(\Gdbots\QueryParser\QueryLexer::T_HASHTAG);

        $this->assertEquals(2, count($hasttags));
        $this->assertEquals('phrase', $hasttags[0]->getToken());
        $this->assertEquals('boost', $hasttags[1]->getToken());
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
