<?php

namespace Gdbots\Tests\QueryParser\Parser;

use Gdbots\QueryParser\QueryLexer;

class QueryLexerTest extends \PHPUnit_Framework_TestCase
{
    /** QueryLexer */
    protected $scanner;

    public function setUp()
    {
        $this->scanner = new QueryLexer();
        $this->scanner->setIgnoreOperators(false);
    }

    public function tearDown()
    {
        $this->scanner = null;
    }

    public function testReadInputString()
    {
        $this->scanner->readString('phrase');

        $this->assertEquals('', $this->scanner->getProcessedData());
        $this->assertEquals('phrase', $this->scanner->getRemainingData());
        $this->assertEquals(0, $this->scanner->getPosition());
    }

    /**
     * @dataProvider getTestNextWithOneTokenDataprovider
     */
    public function testNextWithOneToken($string, $type)
    {
        $this->scanner->readString($string);

        $this->assertEquals($type, $this->scanner->next());
    }

    public function getTestNextWithOneTokenDataprovider()
    {
        return [
            ['phrase', QueryLexer::T_WORD],
            ['', QueryLexer::T_EOI],
            ['-phrase', QueryLexer::T_EXCLUDE],
            ['+phrase', QueryLexer::T_INCLUDE],
            ['#phrase', QueryLexer::T_HASHTAG],
            ['@phrase', QueryLexer::T_MENTION],
            ['^123', QueryLexer::T_BOOST],
            [':phrase', QueryLexer::T_FILTER],
            [':>phrase', QueryLexer::T_FILTER],
            [':<phrase', QueryLexer::T_FILTER],
            [':!phrase', QueryLexer::T_FILTER],
            ['OR', QueryLexer::T_OR_OPERATOR],
            ['AND', QueryLexer::T_AND_OPERATOR],
            ['"phrase"', QueryLexer::T_PHRASE],
            ['"', QueryLexer::T_QUOTE]
        ];
    }

    public function testGetTokenTypeText()
    {
        $this->assertEquals('PHRASE', $this->scanner->getTokenTypeText(QueryLexer::T_PHRASE));
    }

    public function testGetTokenTypeTextCurrenToken()
    {
        $this->scanner->readString('phrase');
        $this->scanner->next();

        $this->assertEquals('WORD', $this->scanner->getTokenTypeText());
    }
}
