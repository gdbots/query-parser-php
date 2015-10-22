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
            ['"phrase"', 'Gdbots\QueryParser\Node\Phrase'],
            ['country:"United State"', 'Gdbots\QueryParser\Node\ExplicitTerm'],
            ['phrase^123', 'Gdbots\QueryParser\Node\ExplicitTerm'],
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

    /**
     * @dataProvider getTestParseWithPrintoutDataprovider
     */
    public function testParseQuery($query, $expected)
    {
        $this->parser->readString($query, true);
        $query = $this->parser->parse();

        $output =  $this->getPrintContent($query);
        $output = preg_replace("/[\r\n]+/", '', $output);
        $output = preg_replace('/\s+/', '', $output);

        $this->assertEquals($expected, $output);
    }

    public function getTestParseWithPrintoutDataprovider()
    {
        return [
            ['##one', 'Hashtag>Word:one'],
            ['#one #two #three', 'Or>Hashtag>>Word:one>Hashtag>>Word:two>Hashtag>>Word:three'],
            ['#one#two##three', 'Or>Hashtag>>Word:one>Hashtag>>Word:two>Hashtag>>Word:three'],
            ['#one!', 'Hashtag>Word:one'],
            ['"#one^7 #two#three ##four!"', 'Phrase:#one^7#two#three##four!'],
            ['a^b', 'Or>Word:a>Word:b'],
            ['a^^2', 'Term:a^2'],
            ['"abc"^def', 'Or>Phrase:abc>Word:def'],
            ['"abc"^2"def ^ghi"jkl^mno^8', 'Or>Term:abc^2>Phrase:def^ghi>Word:jkl>Term:mno^8'],
            ['abc^2def', 'Or>Term:abc^2>Word:def'],
            ['#a^2', 'Term:^2>Hashtag>>Word:a'],
            ['a#b', 'Or>Word:a>Hashtag>>Word:b'],
            ['+a-b-c -d-e+f', 'Or>IncludeTerm>>Word:a-b-c>ExcludeTerm>>Word:d>Word:e>Word:f'],
            ['+a-b - c -d - e+f', 'Or>IncludeTerm>>Word:a-b>Phrase:->Word:c>ExcludeTerm>>Word:d>Phrase:->Word:e>Word:f'],
            ['"abc""def""ghi', 'Or>Phrase:abc>Phrase:def>Word:ghi'],
            ['"abc"def', 'Or>Phrase:abc>Word:def'],
            ['"abc"def"', 'Or>Phrase:abc>Word:def'],
            ['abc"def', 'Or>Word:abc>Word:def'],
            ['abc"def ghi"@j"@k l', 'Or>Word:abc>Phrase:defghi>Mention>>Word:j>Mention>>Word:k>Word:l'],
            ['#a#b@c @d#e', 'Or>Hashtag>>Word:a>Hashtag>>Word:b>Mention>>Word:c>Mention>>Word:d>Hashtag>>Word:e'],
            ['(a b)^2', 'Or>Word:a>Term:b^2'],
            ['+(a b c)-(d e f)^2', 'Or>IncludeTerm>>Word:a>Word:b>Word:c>Word:d>Word:e>Term:f^2'],
            ['a b:', 'Or>Word:a>Word:b'],
            ['http://a.com a:>500', 'Or>Word:http://a.com>Term:a:>500'],
            ['a (b/c d)^2 Father and Daughter', 'Or>Word:a>Word:b>Word:c>Term:d^2>Word:Father>Word:and>Word:Daughter'],
            ['a:>b^2abc', 'Or>Term:^2>>Term:a:>b>Word:abc'],
            ['a + b', 'Or>Word:a>Phrase:+>Word:b',],
            ['+(a:>b)-c:>d -e:<f', 'Or>IncludeTerm>>Term:a:>b>Term:c:>d>ExcludeTerm>>Term:e:<f'],
            ['a:(a b)^2', 'Or>Term:a:a>Term:b^2'],
            [' #+a #-b', 'Or>Word:a>Word:b']
        ];
    }

    public function testParseTextWithUnclosedQuotes()
    {
        $this->parser->readString('"phrase');
        $query = $this->parser->parse();

        $this->assertInstanceOf('Gdbots\QueryParser\Node\Word', $query);
    }

    public function testParseIllegalCharacterError()
    {
        $this->parser->readString('$phrase');
        $query = $this->parser->parse();
        $this->assertNull($query);
        $this->assertTrue($this->parser->hasErrors());

        $this->parser->readString('phrase && word || "text"');
        $query = $this->parser->parse();
        $this->assertNull($query);
        $this->assertTrue($this->parser->hasErrors());
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

    public function testParseFilterWithBoost()
    {
        $this->parser->readString('table.fieldName:value^123');
        $query = $this->parser->parse();

        $output = " Term: ^ 123
> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQuery()
    {
        $this->parser->readString('(("phrase" #phrase) table.fieldName:value)^123');
        $query = $this->parser->parse();

        $output = " Term: ^ 123
> Subexpression
>> Or
>>> Subexpression
>>>> Or
>>>>> Phrase: phrase
>>>>> Hashtag
>>>>>> Word: phrase
>>> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQueryUsingOperator()
    {
        $this->parser->readString('(("phrase" OR #phrase) AND table.fieldName:value)^123');
        $query = $this->parser->parse();

        $output = " Term: ^ 123
> Subexpression
>> And
>>> Subexpression
>>>> Or
>>>>> Phrase: phrase
>>>>> Hashtag
>>>>>> Word: phrase
>>> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQueryWithIgnoreOperator()
    {
        $this->parser->readString('(("phrase" OR #phrase) AND table.fieldName:value)^123', true);
        $query = $this->parser->parse();

        $output = " Or
> Phrase: phrase
> Hashtag
>> Word: phrase
> Term: ^ 123
>> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseEmoji()
    {
        $this->parser->readString('#emoji 💩 AND 🍦 OR 😳');
        $query = $this->parser->parse();

        $output = " And
> Or
>> Hashtag
>>> Word: emoji
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
