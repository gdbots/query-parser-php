<?php

namespace Gdbots\Tests\QueryParser\Node;

use Gdbots\QueryParser\Node\Word;

abstract class ExpressionListTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $word1;

    /** @var string */
    protected $word2;

    /** @var ExpressionList */
    protected $expressionList;

    public function setUp()
    {
        $this->word1 = new Word('word1');
        $this->word2 = new Word('word2');
    }

    public function tearDown()
    {
        $this->word1 = null;
        $this->word2 = null;
        $this->expressionList = null;
    }

    public function testGetExpressions()
    {
        $this->assertEquals([$this->word1, $this->word2], $this->expressionList->getExpressions());
    }

    public function testCount()
    {
        $this->assertEquals(2, count($this->expressionList));
    }

    public function testIterator()
    {
        $items = [
            $this->word1,
            $this->word2
        ];

        foreach ($this->expressionList as $key => $word) {
            $this->assertSame($word, $items[$key]);
        }
    }
}
