<?php

namespace Gdbots\Tests\QueryParser\Parser;

use Gdbots\QueryParser\Parser\QueryScanner;

class QueryScannerTest extends \PHPUnit_Framework_TestCase
{
    /** QueryScanner */
    protected $scanner;

    public function setUp()
    {
        $this->scanner = new QueryScanner();
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
            ['phrase', QueryScanner::T_WORD],
            ['', QueryScanner::T_EOI],
            ['(', QueryScanner::T_OPEN_PARENTHESIS],
            [')', QueryScanner::T_CLOSE_PARENTHESIS],
            ['-', QueryScanner::T_EXCLUDE],
            ['+', QueryScanner::T_INCLUDE],
            ['#', QueryScanner::T_HASHTAG],
            ['@', QueryScanner::T_MENTION],
            ['^', QueryScanner::T_BOOST],
            [':', QueryScanner::T_COLON],
            ['OR', QueryScanner::T_OR_OPERATOR],
            ['AND', QueryScanner::T_AND_OPERATOR],
            ['"phrase"', QueryScanner::T_TEXT],
            ['"', QueryScanner::T_QUOTE]
       ];
    }

    public function testGetTokenTypeText()
    {
        $this->assertEquals('TEXT', $this->scanner->getTokenTypeText(QueryScanner::T_TEXT));
    }

    public function testGetTokenTypeTextCurrenToken()
    {
        $this->scanner->readString('phrase');
        $this->scanner->next();

        $this->assertEquals('WORD', $this->scanner->getTokenTypeText());
    }
}
