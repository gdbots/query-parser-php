<?php

namespace Gdbots\Tests\QueryParser\Node;

use Gdbots\QueryParser\Node\AndExpressionList;

class AndExpressionListTest extends ExpressionListTest
{
    public function setUp()
    {
        parent::setUp();

        $this->expressionList = new AndExpressionList([$this->word1, $this->word2]);
    }

    public function testToArray()
    {
        $this->assertInternalType('array', $this->expressionList->toArray());
    }
}
