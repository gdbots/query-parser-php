<?php

namespace Gdbots\Tests\QueryParser\Parser;

use Gdbots\QueryParser\Node\QueryItem;
use Gdbots\QueryParser\Parser\QueryParser;
use Gdbots\QueryParser\Parser\QueryScanner;
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
            ['"phrase"', 'Gdbots\QueryParser\Node\Text'],
            ['country:"United State"', 'Gdbots\QueryParser\Node\ExplicitTerm'],
            ['phrase^boost', 'Gdbots\QueryParser\Node\ExplicitTerm'],
            ['-phrase', 'Gdbots\QueryParser\Node\ExcludeTerm'],
            ['+phrase', 'Gdbots\QueryParser\Node\IncludeTerm'],
            ['#phrase', 'Gdbots\QueryParser\Node\Hashtag'],
            ['@phrase', 'Gdbots\QueryParser\Node\Mention'],
            ['phrase word', 'Gdbots\QueryParser\Node\OrExpressionList'],
            ['phrase OR word', 'Gdbots\QueryParser\Node\OrExpressionList'],
            ['phrase AND word', 'Gdbots\QueryParser\Node\AndExpressionList'],
            ['(phrase OR word)', 'Gdbots\QueryParser\Node\Subexpression']
        ];
    }

    public function testParseTextWithUnclosedQuotes()
    {
        $this->parser->readString('"phrase');
        $query = $this->parser->parse();

        $this->assertInstanceOf('Gdbots\QueryParser\Node\Word', $query);
    }

    public function testParseInvalidExcludeTermError()
    {
        $this->parser->readString('-"phrase');
        $query = $this->parser->parse();
        $this->assertNull($query);
        $this->assertTrue($this->parser->hasErrors());
    }

    public function testParseMultiHashtags()
    {
        $this->parser->readString('#one #two #three');
        $query = $this->parser->parse();

        $output = " Or
> Hashtag
>> Word: one
> Hashtag
>> Word: two
> Hashtag
>> Word: three
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseDuplicateHashtags()
    {
        $this->parser->readString('##phrase');
        $query = $this->parser->parse();

        $output = " Hashtag
> Word: phrase
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseCompareWithBoost()
    {
        $this->parser->readString('table.fieldName:value^boost');
        $query = $this->parser->parse();

        $output = " Term: ^ boost
> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQuery()
    {
        $this->parser->readString('(("phrase" #phrase) table.fieldName:value)^boost');
        $query = $this->parser->parse();

        $output = " Term: ^ boost
> Subexpression
>> Or
>>> Text: phrase
>>> Hashtag
>>>> Word: phrase
>>> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQueryUsingOperator()
    {
        $this->parser->readString('(("phrase" OR #phrase) AND table.fieldName:value)^boost');
        $query = $this->parser->parse();

        $output = " Term: ^ boost
> Subexpression
>> And
>>> Or
>>>> Text: phrase
>>>> Hashtag
>>>>> Word: phrase
>>> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQueryWithIgnoreOperator()
    {
        $this->parser->readString('(("phrase" OR #phrase) AND table.fieldName:value)^boost', true);
        $query = $this->parser->parse();

        $output = " Or
> Text: phrase
> Hashtag
>> Word: phrase
> Term: ^ boost
>> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseGetHashtagQueryItems()
    {
        $this->parser->readString('(("phrase" OR #phrase) AND table.fieldName:value) #boost');
        $query = $this->parser->parse();

        $hasttags = $query->getQueryItemsByTokenType(\Gdbots\QueryParser\Parser\QueryScanner::T_HASHTAG);

        $this->assertEquals(2, count($hasttags));
        $this->assertEquals('phrase', $hasttags[0]->getExpression()->getToken());
        $this->assertEquals('boost', $hasttags[1]->getExpression()->getToken());
    }

    /**
     * @return string
     */
    private function getPrintContent(QueryItem $query)
    {
        ob_start();

        $query->accept($this->printer);

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
}
