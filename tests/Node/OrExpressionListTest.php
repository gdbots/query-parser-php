<?php

namespace Gdbots\Tests\QueryParser\Node;

use Gdbots\QueryParser\Node\OrExpressionList;

class OrExpressionListTest extends ExpressionListTest
{
    public function setUp()
    {
        parent::setUp();

        $this->expressionList = new OrExpressionList(array($this->word1, $this->word2));
    }

    public function testToArray()
    {
        $this->assertInternalType('array', $this->expressionList->toArray());
    }
}
