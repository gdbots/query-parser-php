<?php

namespace Gdbots\Tests\QueryParser\Node;

use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\Node\IncludeTerm;

class IncludeTermTest extends \PHPUnit_Framework_TestCase
{
    /** @var Word */
    protected $word;

    /** @var IncludeTerm */
    protected $includeTerm;

    public function setUp()
    {
        $this->word = new Word('phrase');
        $this->includeTerm = new IncludeTerm($this->word);
    }

    public function tearDown()
    {
        $this->word = null;
        $this->includeTerm = null;
    }

    public function testGetSubexpression()
    {
        $this->assertSame($this->word, $this->includeTerm->getSubexpression());
    }

    public function testToArray()
    {
        $array = [
            'Operator' => 'Include Term',
            'Expression' => $this->word
        ];

        $this->assertEquals($array, $this->includeTerm->toArray());
    }
}
