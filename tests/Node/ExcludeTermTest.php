<?php

namespace Gdbots\Tests\QueryParser\Node;

use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\Node\ExcludeTerm;

class ExcludeTermTest extends \PHPUnit_Framework_TestCase
{
    /** @var Word */
    protected $word;

    /** @var ExcludeTerm */
    protected $excludeTerm;

    public function setUp()
    {
        $this->word = new Word('phrase');
        $this->excludeTerm = new ExcludeTerm($this->word);
    }

    public function tearDown()
    {
        $this->word = null;
        $this->excludeTerm = null;
    }

    public function testGetSubexpression()
    {
        $this->assertSame($this->word, $this->excludeTerm->getSubexpression());
    }

    public function testToArray()
    {
        $array = [
            'Operator' => 'Exclude Term',
            'Expression' => $this->word
        ];

        $this->assertEquals($array, $this->excludeTerm->toArray());
    }
}
