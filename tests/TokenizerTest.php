<?php

namespace Gdbots\Tests\QueryParser;

use Gdbots\QueryParser\Token as T;
use Gdbots\QueryParser\Tokenizer;

class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Tokenizer */
    protected $tokenizer;

    public function setUp()
    {
        $this->tokenizer = new Tokenizer();
    }

    public function testOnlyWhitespace()
    {
        $this->assertEquals([], $this->tokenizer->scan('      ')->getTokens());
    }

    /**
     * @dataProvider getTestQueries
     *
     * @param string $name
     * @param string $input
     * @param array $expectedTokens
     */
    public function testScan($name, $input, array $expectedTokens)
    {
        // convert the sample 'expected' into token objects.
        foreach ($expectedTokens as $k => $v) {
            if (!is_array($v)) {
                $expectedTokens[$k] = new T($v);
                continue;
            }

            $expectedTokens[$k] = new T($v[0], $v[1]);
        }

        $tokenStream = $this->tokenizer->scan($input);
        $this->assertEquals($expectedTokens, $tokenStream->getTokens(), "Test query [{$name}] with input [{$input}] failed.");
    }

    /**
     * @return array
     */
    public function getTestQueries()
    {
        return require __DIR__.'/Fixtures/test-queries.php';
    }
}
