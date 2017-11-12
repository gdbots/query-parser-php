<?php
declare(strict_types=1);

namespace Gdbots\Tests\QueryParser\Builder;

use Gdbots\QueryParser\Builder\XmlQueryBuilder;
use Gdbots\QueryParser\QueryParser;
use Gdbots\QueryParser\Node\Node;
use PHPUnit\Framework\TestCase;

class XmlQueryBuilderTest extends TestCase
{
    /** @var QueryParser */
    protected $parser;

    /** @var XmlQueryBuilder */
    protected $builder;

    public function setUp()
    {
        $this->parser = new QueryParser();
        $this->builder = new XmlQueryBuilder();
    }

    /**
     * @dataProvider getTestQueries
     *
     * @param string $name
     * @param string $input
     * @param null   $ignored
     * @param Node[] $expectedNodes
     */
    public function testToSimpleXmlElement(string $name, string $input, $ignored, array $expectedNodes = []): void
    {
        $this->builder->addParsedQuery($this->parser->parse($input));
        $xml = $this->builder->toSimpleXmlElement();
        $expectedNodeCount = count($expectedNodes);

        if ($expectedNodeCount && $xml->count() < $expectedNodeCount) {
            $this->fail('Failed to generate SimpleXmlElement from: ' . $input);
        }

        $this->assertSame($expectedNodeCount, $xml->count());

        /** @var \SimpleXmlElement $child */
        $i = 0;
        foreach ($xml->children() as $child) {
            if (!isset($expectedNodes[$i])) {
                $this->fail('Xml contains unexpected nodes');
            }

            $node = $expectedNodes[$i];
            $this->assertEquals(
                $node::NODE_TYPE,
                $child->getName(),
                "Test query [{$name}] with input [{$input}] failed."
            );

            $i++;
        }
    }

    /**
     * @return array
     */
    public function getTestQueries(): array
    {
        return require __DIR__ . '/../Fixtures/test-queries.php';
    }
}
