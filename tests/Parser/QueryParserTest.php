<?php

namespace Gdbots\Tests\QueryParser\Parser;

use Gdbots\QueryParser\Parser\QueryParser;

class QueryParserTest extends \PHPUnit_Framework_TestCase
{
    /** QueryParser */
    protected $parser;

    public function setUp()
    {
        $this->parser = new QueryParser();
    }

    public function tearDown()
    {
        $this->parser = null;
    }

    /**
     * @dataProvider getTestParseWithOneClassDataprovider
     */
    public function testParseNode($string, $class)
    {
        $this->parser->readString($string);
        $result = $this->parser->parse();

        $this->assertInstanceOf($class, $result);
    }

    public function getTestParseWithOneClassDataprovider()
    {
        return array(
            array('phrase', 'Gdbots\QueryParser\Node\Word'),
            array('"phrase"', 'Gdbots\QueryParser\Node\Text'),
            array('country:"United State"', 'Gdbots\QueryParser\Node\ExplicitTerm'),
            array('phrase^boost', 'Gdbots\QueryParser\Node\ExplicitTerm'),
            array('-phrase', 'Gdbots\QueryParser\Node\ExcludeTerm'),
            array('+phrase', 'Gdbots\QueryParser\Node\IncludeTerm'),
            array('#phrase', 'Gdbots\QueryParser\Node\Hashtag'),
            array('@phrase', 'Gdbots\QueryParser\Node\Mention'),
            array('phrase word', 'Gdbots\QueryParser\Node\OrExpressionList'),
            array('phrase OR word', 'Gdbots\QueryParser\Node\OrExpressionList'),
            array('phrase AND word', 'Gdbots\QueryParser\Node\AndExpressionList'),
            array('(phrase)', 'Gdbots\QueryParser\Node\Subexpression'),

        );
    }

    public function testParseTextWithUnclosedQuotes()
    {
        $this->parser->readString('"phrase');
        $result = $this->parser->parse();
        $this->assertInstanceOf('Gdbots\QueryParser\Node\Word', $result);
    }

    public function testParseInvalidExcludeTermError()
    {
        $this->parser->readString('-"phrase');
        $result = $this->parser->parse();
        $this->assertNull($result);
        $this->assertTrue($this->parser->hasErrors());
    }
}
