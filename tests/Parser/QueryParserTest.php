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
    public function testParseNode($string, $class, $isList = false)
    {
        $this->parser->readString($string);
        $result = $this->parser->parse();

        $expressions = $result->getExpressions();

        $this->assertInstanceOf($class, $isList ? $result : $expressions[0]);
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
            ['phrase word', 'Gdbots\QueryParser\Node\OrExpressionList', true],
            ['phrase OR word', 'Gdbots\QueryParser\Node\OrExpressionList', true],
            ['phrase AND word', 'Gdbots\QueryParser\Node\AndExpressionList', true],
            ['(phrase)', 'Gdbots\QueryParser\Node\Subexpression']
        ];
    }

    public function testMulipleHashtagSymbols()
    {
        $expectedResult = "Or>Hashtag>>Word:one";

        $this->parser->readString('##one');
        $results = $this->parser->parse();
        $this->assertInstanceOf('Gdbots\QueryParser\Node\OrExpressionList', $results);



        $output = $this->getPrintContent($results);

        $output = preg_replace("/[\r\n]+/", "", $output);
        $output = preg_replace("/\s+/", '', $output);

        $this->assertEquals($expectedResult, $output);
    }

   public function testMultipleHashtagsNoSpace()
    {
     $expectedResult = "Or>Hashtag>>Word:one>Hashtag>>Word:two>Hashtag>>Word:three";

     $this->parser->readString('#one#two##three');
     $results = $this->parser->parse();
     $this->assertInstanceOf('Gdbots\QueryParser\Node\OrExpressionList', $results);

     $output = $this->getPrintContent($results);

     $output = preg_replace("/[\r\n]+/", "", $output);
     $output = preg_replace("/\s+/", '', $output);

     $this->assertEquals($expectedResult, $output);

    }

    public function testInvalidHashtagCharacter()
    {
        $expectedResult = "Or>Hashtag>>Word:one";

        $this->parser->readString('#one!');
        $results = $this->parser->parse();
        $this->assertInstanceOf('Gdbots\QueryParser\Node\OrExpressionList', $results);

        $output = $this->getPrintContent($results);

        $output = preg_replace("/[\r\n]+/", "", $output);
        $output = preg_replace("/\s+/", '', $output);

        $this->assertEquals($expectedResult, $output);
    }

    public function testHashtagInQuotes()
    {
        $expectedResult = "Or>Text:#one #two#three ##four";

        $this->parser->readString('"#one #two#three ##four"');
        $results = $this->parser->parse();

        $this->assertInstanceOf('Gdbots\QueryParser\Node\OrExpressionList', $results);

        $output = $this->getPrintContent($results);

        $output = preg_replace("/[\r\n]+/", "", $output);
        $output = preg_replace("/\s+/", '', $output);

        $this->assertEquals($expectedResult, $output);
    }

    public function testBoostHashtag()
    {
        $expectedResult = "Or>Term:^1>>Hashtag>>>Word:one";

        $this->parser->readString('#one^1');
        $results = $this->parser->parse();
        $this->assertInstanceOf('Gdbots\QueryParser\Node\OrExpressionList', $results);

        $output = $this->getPrintContent($results);

        $output = preg_replace("/[\r\n]+/", "", $output);
        $output = preg_replace("/\s+/", '', $output);

        $this->assertEquals($expectedResult, $output);
    }

    public function testMultipleBoostSymbolsOnKeyword()
    {
        $expectedResult = "Or>Term:one^1";

        $this->parser->readString('one^^1');
        $results = $this->parser->parse();
        $this->assertInstanceOf('Gdbots\QueryParser\Node\OrExpressionList', $results);

        $output = $this->getPrintContent($results);

        $output = preg_replace("/[\r\n]+/", "", $output);
        $output = preg_replace("/\s+/", '', $output);

        $this->assertEquals($expectedResult, $output);
    }

    public function testBoostWithDigitAndNonDigit()
    {

        $expectedResult = "Or>Term:one^1>Word:abc";

        $this->parser->readString('one^1abc');
        $results = $this->parser->parse();
        $this->assertInstanceOf('Gdbots\QueryParser\Node\OrExpressionList', $results);

        $output = $this->getPrintContent($results);

        $output = preg_replace("/[\r\n]+/", "", $output);
        $output = preg_replace("/\s+/", '', $output);

        $this->assertEquals($expectedResult, $output);
    }

    public function testBoostWithNonDigit()
    {

        $expectedResult = "Or>Word:one>Word:two";

        $this->parser->readString('one^two');
        $results = $this->parser->parse();
        $this->assertInstanceOf('Gdbots\QueryParser\Node\OrExpressionList', $results);

        $output = $this->getPrintContent($results);

        $output = preg_replace("/[\r\n]+/", "", $output);
        $output = preg_replace("/\s+/", '', $output);

        $this->assertEquals($expectedResult, $output);
    }

/* public function testParseTextWithUnclosedQuotes()
 {
     $this->parser->readString('"phrase');
     $result = $this->parser->parse();
     $expressions = $result->getExpressions(QueryScanner::T_WORD);
     $this->assertInstanceOf('Gdbots\QueryParser\Node\Word', $expressions[0]);
 }

 public function testParseInvalidExcludeTermError()
 {
     $this->parser->readString('-"phrase');
     $result = $this->parser->parse();
     $this->assertNull($result);
     $this->assertTrue($this->parser->hasErrors());
 }

 public function testParseMultiHashtags()
 {
     $this->parser->readString('#one #two #three');
     $result = $this->parser->parse();

     $output = " Or
> Hashtag
>> Word: one
> Hashtag
>> Word: two
> Hashtag
>> Word: three
";

     $this->assertEquals($output, $this->getPrintContent($result));
 }*/

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
