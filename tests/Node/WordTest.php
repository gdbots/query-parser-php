<?php

namespace Gdbots\Tests\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Node\Word;

class WordTest extends \PHPUnit_Framework_TestCase
{
    /** @var Word */
    protected $word;

    public function setUp()
    {
        $this->word = new Word('phrase');
    }

    public function tearDown()
    {
        $this->word = null;
    }

    public function testGetToken()
    {
        $this->assertEquals('phrase', $this->word->getToken());
    }

    public function testGetTokenType()
    {
        $this->assertEquals(QueryLexer::T_WORD, $this->word->getTokenType());
    }

    public function testToArray()
    {
        $array = [
            'Expression' => 'Word',
            'Term' => 'phrase'
        ];

        $this->assertEquals($array, $this->word->toArray());
    }
}
