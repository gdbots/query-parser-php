<?php

namespace Gdbots\Tests\QueryParser;

use Gdbots\QueryParser\QueryParser;

class QueryParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var QueryParser */
    protected $parser;

    public function setUp()
    {
        $this->parser = new QueryParser();
    }

    /**
     * @dataProvider getTestQueries
     *
     * @param string $name
     * @param string $input
     * @param null $ignored
     * @param array $expectedNodes
     */
    public function testParse($name, $input, $ignored, array $expectedNodes = [])
    {
        if (!isset($expectedNodes) || empty($expectedNodes)) {
            return;
        }

        $result = $this->parser->parse($input);
        $this->assertEquals($expectedNodes, $result->getNodes(), "Test query [{$name}] with input [{$input}] failed.");
    }

    /**
     * @return array
     */
    public function getTestQueries()
    {
        return require __DIR__.'/Fixtures/test-queries.php';
    }
}
