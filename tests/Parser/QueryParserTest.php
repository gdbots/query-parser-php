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


    public function queryDataprovider()
    {
        return [
            ['#a#b#c #d^2', array('hashtags'=>array(array('token'=>'a','attribute'=>array('boosted'=>false,'negated'=>false,'required'=>false)),array('token'=>'b'),array('token'=>'c')),
                            'phrases'=>array(),
                            'words'=>array(),
                            'filters'=>array(),
                            'mentions'=>array()
                              )]
        ];
    }

    /**
     * @dataProvider queryDataprovider
     */
    public function testQueryParser($query, $expected){

        $this->parser->readString($query);
        $query = $this->parser->parse();
        //print_r($query);
        //exit;

        //$hashtags = $query->getQueryItemsByTokenType(\Gdbots\QueryParser\Parser\QueryScanner::T_HASHTAG);
        $boost = $query->getQueryItemsByTokenType();
        $include = $query->getQueryItemsByTokenType(\Gdbots\QueryParser\Parser\QueryScanner::T_EXCLUDE);
        $exclude = $query->getQueryItemsByTokenType(\Gdbots\QueryParser\Parser\QueryScanner::T_INCLUDE);
        //print_r($hashtags);
        print_r($boost);

        exit;



        $phrases = $query->getQueryItemsByTokenType(\Gdbots\QueryParser\Parser\QueryScanner::T_PHRASE);
        $words = $query->getQueryItemsByTokenType(\Gdbots\QueryParser\Parser\QueryScanner::T_WORD);
        $filters = $query->getQueryItemsByTokenType(\Gdbots\QueryParser\Parser\QueryScanner::T_FILTER);
        $mention = $query->getQueryItemsByTokenType(\Gdbots\QueryParser\Parser\QueryScanner::T_MENTION);
        $hashtagArray=array();
        $phraseArray=array();
        $wordArray=array();
        $filterArray=array();
        $mentionArray=array();


        foreach($hashtags as $hashtag){
            $hashtagArray[]=array('token'=>$hashtag->getExpression()->getToken(),'attribute'=>array('boosted'=>$hashtag->getExpression()->isBoosted(),));


        }


        $this->assertEquals(2, count($hasttags));
        $this->assertEquals('phrase', $hasttags[0]->getExpression()->getToken());
        $this->assertEquals('boost', $hasttags[1]->getExpression()->getToken());
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
>>>>>>> excel/master
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
>>>>>>> excel/master
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
