<?php
declare(strict_types=1);

namespace Gdbots\Tests\QueryParser;

use Gdbots\QueryParser\QueryParser;
use PHPUnit\Framework\TestCase;

class QueryParserTest extends TestCase
{
    protected ?QueryParser $parser = null;

    public function setUp(): void
    {
        $this->parser = new QueryParser();
    }

    /**
     * @dataProvider getTestQueries
     *
     * @param string $name
     * @param string $input
     * @param null   $ignored
     * @param array  $expectedNodes
     */
    public function testParse(string $name, string $input, $ignored, array $expectedNodes = []): void
    {
        $result = $this->parser->parse($input);
        $this->assertEquals($expectedNodes, $result->getNodes(), "Test query [{$name}] with input [{$input}] failed.");
    }

    public function getTestQueries(): array
    {
        return require __DIR__ . '/Fixtures/test-queries.php';
    }
}
