<?php

namespace Gdbots\Tests\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Node\Phrase;

class PhraseTest extends \PHPUnit_Framework_TestCase
{
    /** @var Phrase */
    protected $phrase;

    public function setUp()
    {
        $this->phrase = new Phrase('"phrase"');
    }

    public function tearDown()
    {
        $this->phrase = null;
    }

    public function testGetToken()
    {
        $this->assertEquals('phrase', $this->phrase->getToken());
    }

    public function testGetQuotedText()
    {
        $this->assertEquals('"phrase"', $this->phrase->getQuotedText());
    }

    public function testStripQuotes()
    {
        $this->assertEquals('phrase', $this->phrase->stripQuotes('"phrase"'));
        $this->assertEquals('', $this->phrase->stripQuotes(''));
    }

    public function testGetTokenType()
    {
        $this->assertEquals(QueryLexer::T_PHRASE, $this->phrase->getTokenType());
    }

    public function testToArray()
    {
        $array = [
            'Expression' => 'Phrase',
            'Term' => 'phrase'
        ];

        $this->assertEquals($array, $this->phrase->toArray());
    }
}
