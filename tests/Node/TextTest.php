<?php

namespace Gdbots\Tests\QueryParser\Node;

use Gdbots\QueryParser\Parser\QueryScanner;
use Gdbots\QueryParser\Node\Text;

class TextTest extends \PHPUnit_Framework_TestCase
{
    /** @var Text */
    protected $text;

    public function setUp()
    {
        $this->text = new Text('"phrase"');
    }

    public function tearDown()
    {
        $this->text = null;
    }

    public function testGetToken()
    {
        $this->assertEquals('phrase', $this->text->getToken());
    }

    public function testGetQuotedText()
    {
        $this->assertEquals('"phrase"', $this->text->getQuotedText());
    }

    public function testStripQuotes()
    {
        $this->assertEquals('phrase', $this->text->stripQuotes('"phrase"'));
        $this->assertEquals('', $this->text->stripQuotes(''));
    }

    public function testGetTokenType()
    {
        $this->assertEquals(QueryScanner::T_TEXT, $this->text->getTokenType());
    }

    public function testToArray()
    {
        $array = array(
            'Expression' => 'Text',
            'Term' => 'phrase'
        );

        $this->assertEquals($array, $this->text->toArray());
    }
}
