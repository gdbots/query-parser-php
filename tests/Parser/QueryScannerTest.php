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
        return array(
            array('phrase', QueryScanner::T_WORD),
            array('', QueryScanner::T_EOI),
            array('(', QueryScanner::T_OPEN_PARENTHESIS),
            array(')', QueryScanner::T_CLOSE_PARENTHESIS),
            array('-', QueryScanner::T_EXCLUDE),
            array('+', QueryScanner::T_INCLUDE),
            array('#', QueryScanner::T_HASHTAG),
            array('@', QueryScanner::T_MENTION),
            array('^', QueryScanner::T_BOOST),
            array(':', QueryScanner::T_COLON),
            array('OR', QueryScanner::T_OR_OPERATOR),
            array('AND', QueryScanner::T_AND_OPERATOR),
            array('"phrase"', QueryScanner::T_TEXT),
            array('"', QueryScanner::T_QUOTE),
       );
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
