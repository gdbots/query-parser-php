<?php

namespace Gdbots\Tests\QueryParser\Parser;

use Gdbots\QueryParser\Node;
use Gdbots\QueryParser\Parser\QueryParser;
use Gdbots\QueryParser\Parser\QueryScanner;
use Gdbots\QueryParser\Visitor\QueryItemPrinter;

class QueryParserTest extends \PHPUnit_Framework_TestCase
{
    /** QueryParser */
    protected $parser;

    /** QueryItemPrinter */
    protected $printer;

    public function setUp()
    {
        $this->parser = new QueryParser();
        $this->printer = new QueryItemPrinter();
    }

    public function tearDown()
    {
        $this->parser = null;
        $this->printer = null;
    }

    /**
     * @dataProvider getTestParseWithOneClassDataprovider
     */
    public function testParseNode($string, $class)
    {
        $this->parser->readString($string);
        $query = $this->parser->parse();

        $this->assertInstanceOf($class, $query);
    }

    public function getTestParseWithOneClassDataprovider()
    {
        return [
            ['phrase', 'Gdbots\QueryParser\Node\Word'],
            ['"phrase"', 'Gdbots\QueryParser\Node\Phrase'],
            ['country:"United State"', 'Gdbots\QueryParser\Node\ExplicitTerm'],
            ['phrase^123', 'Gdbots\QueryParser\Node\ExplicitTerm'],
            ['-phrase', 'Gdbots\QueryParser\Node\ExcludeTerm'],
            ['+phrase', 'Gdbots\QueryParser\Node\IncludeTerm'],
            ['#phrase', 'Gdbots\QueryParser\Node\Hashtag'],
            ['@phrase', 'Gdbots\QueryParser\Node\Mention'],
            ['phrase word', 'Gdbots\QueryParser\Node\OrExpressionList'],
            ['phrase OR word', 'Gdbots\QueryParser\Node\OrExpressionList'],
            ['phrase AND word', 'Gdbots\QueryParser\Node\AndExpressionList'],
            ['(phrase OR word)', 'Gdbots\QueryParser\Node\Subexpression']
        ];
    }

    /**
     * @dataProvider getTestParseWithPrintoutDataprovider
     */
    public function testParseQuery($string, $print)
    {
        $this->parser->readString($string, true);
        $query = $this->parser->parse();

        $output =  $this->getPrintContent($query);
        $output = preg_replace("/[\r\n]+/", '', $output);
        $output = preg_replace('/\s+/', '', $output);

        $this->assertEquals($print, $output);
    }

    /**
     * @dataProvider getTestParseWithPrintoutDataprovider
     */
    public function testGetQueryItemsByTokenType($string, $print, $itemCount)
    {
        $this->parser->readString($string, true);
        $query = $this->parser->parse();

        $tokens = $query->getQueryItemsByTokenType();

        $totalCount = 0;
        foreach ($tokens as $tokenBuckets) {
            $totalCount += count($tokenBuckets);
        }

        $runningCount = 0;
        foreach ($itemCount as $key => $value) {
            $runningCount += $value;

            $this->assertArrayHasKey($key, $tokens);
            $this->assertEquals($value, count($tokens[$key]));
        }

        $this->assertEquals($totalCount, $runningCount);
    }

    /**
     * @dataProvider getTestParseWithPrintoutDataprovider
     */
    public function testGetQueryItemsByTokenTypeUsingType($string, $print, $itemCount)
    {
        $this->parser->readString($string, true);
        $query = $this->parser->parse();

        foreach ($itemCount as $key => $value) {
            $tokens = $query->getQueryItemsByTokenType(constant('Gdbots\QueryParser\Parser\QueryScanner::T_'.$key));
            $count = count($tokens);
            $this->assertEquals($value, $count);
        }
    }

    /**
     * @dataProvider getTestParseWithPrintoutDataprovider
     */
    public function testQueryParseObject($string, $print, $itemCount, $expected)
    {

        $tokenTypes = ['FILTER', 'HASHTAG', 'MENTION', 'PHRASE', 'URL', 'WORD'];

            $this->parser->readString($string, true);
            $query = $this->parser->parse();
            $allTokenArray = [];

            foreach ($tokenTypes as $tokenType) {
                $tokens = $query->getQueryItemsByTokenType(constant('Gdbots\QueryParser\Parser\QueryScanner::T_' . $tokenType));

                if (!empty($tokens)) {
                    foreach ($tokens as $token) {
                        $boosted = false;
                        $included = false;
                        $excluded = false;
                        $tokenArray = [];

                        if (!($token instanceof Node\SimpleTerm)) {
                            if ($token->getTokenType() === QueryScanner::T_FILTER) {
                                $tokenField = $token->getNominator()->getToken();
                                $tokenValue = $token->getTerm()->getToken();
                                $tokenTypeText = $token->getTokenTypeText();
                                $boosted = $token->getParentTokenType(QueryScanner::T_BOOST, false);
                                $excluded = $token->hasParentTokenType(QueryScanner::T_EXCLUDE);
                                $included = $token->hasParentTokenType(QueryScanner::T_INCLUDE);
                            } else {
                                $tokenValue = $token->getExpression()->getToken();
                                $boosted = $token->getExpression()->getParentTokenType(QueryScanner::T_BOOST, false);
                                $excluded = $token->getExpression()->hasParentTokenType(QueryScanner::T_EXCLUDE);
                                $included = $token->getExpression()->hasParentTokenType(QueryScanner::T_INCLUDE);
                            }
                        } else {
                            $tokenValue = $token->getToken();
                            $boosted = $token->getParentTokenType(QueryScanner::T_BOOST, false);
                            $excluded = $token->hasParentTokenType(QueryScanner::T_EXCLUDE);
                            $included = $token->hasParentTokenType(QueryScanner::T_INCLUDE);
                        }

                        if ($token->getTokenType() === QueryScanner::T_FILTER) {
                            $tokenArray['field'] = $tokenField;
                            $tokenArray['value'] = $tokenValue;
                            $tokenArray['operator'] = $tokenTypeText;
                        } else {
                            $tokenArray['value'] = $tokenValue;
                        }
                        if ($boosted) {
                            $tokenArray['boost'] = $boosted;
                        }
                        if ($excluded) {
                            $tokenArray['exclude'] = true;
                        }
                        if ($included) {
                            $tokenArray['include'] = true;
                        }

                        $allTokenArray[$tokenType][] = $tokenArray;
                    }
                }
            }

           $this->assertEquals($expected, $allTokenArray);
    }

    public function getTestParseWithPrintoutDataprovider()
    {
        return [
                    [
                        '##one',
                        'Hashtag>Word:one',
                        ['HASHTAG' => 1],
                        [
                            'HASHTAG' =>   [
                                ['value' => 'one'],
                            ]
                        ]

                    ],
                    [
                        '#one #two #three',
                        'Or>Hashtag>>Word:one>Hashtag>>Word:two>Hashtag>>Word:three',
                        ['HASHTAG' => 3],
                        [
                            'HASHTAG' =>   [
                                ['value' => 'one'],
                                ['value' => 'two'],
                                ['value' => 'three']
                            ]
                        ]
                    ],
                    [
                        '#one#two##three',
                        'Or>Hashtag>>Word:one>Hashtag>>Word:two>Hashtag>>Word:three',
                        ['HASHTAG' => 3],
                        [
                            'HASHTAG' => [
                                ['value' => 'one'],
                                ['value' => 'two'],
                                ['value' => 'three'],
                            ]
                        ]
                    ],
                    [
                        '#one!',
                        'Or>Hashtag>>Word:one>Word:!',
                        ['HASHTAG' => 1, 'WORD' => 1],
                        [
                            'HASHTAG' => [
                                ['value' => 'one'],
                            ],
                            'WORD' => [
                                ['value' => '!']
                            ]
                        ]
                    ],
                    [
                        '"#one^7 #two#three ##four!"',
                        'Phrase:#one^7#two#three##four!',
                        ['PHRASE' => 1],
                        [
                            'PHRASE' => [
                                ['value' => '#one^7 #two#three ##four!']
                            ]
                        ]
                    ],
                    [
                        'a^b',
                        'Or>Word:a>Word:b',
                        ['WORD' => 2],
                        [
                            'WORD' => [
                                ['value' => 'a'],
                                ['value' => 'b']
                            ]
                        ]
                    ],
                    [
                        'a^^2',
                        'Term:a^2',
                        ['WORD' => 1],
                        [
                            'WORD' => [
                                ['value' => 'a', 'boost' => 2]
                            ]
                        ]
                    ],
                    [
                        '"abc"^def',
                        'Or>Phrase:abc>Word:def',
                        ['PHRASE' => 1, 'WORD' => 1],
                        [
                            'PHRASE' => [
                                ['value' => 'abc']
                            ],
                            'WORD' => [
                                ['value' => 'def']
                            ]
                        ]
                    ],
                    [
                        '"abc"^2"def ^ghi"jkl^mno^8',
                        'Or>Term:abc^2>Phrase:def^ghi>Word:jkl>Term:mno^8',
                        ['PHRASE' => 2, 'WORD' => 2],
                        [
                            'PHRASE' => [
                                ['value' => 'abc', 'boost' => 2],
                                ['value' => 'def ^ghi']
                            ],
                            'WORD' => [
                                ['value' => 'jkl'],
                                ['value' => 'mno', 'boost' => 8]
                            ]
                        ]
                    ],
                    [
                        'abc^2def',
                        'Or>Term:abc^2>Word:def',
                        ['WORD' => 2],
                        [
                            'WORD' => [
                                ['value' => 'abc', 'boost' => 2],
                                ['value' => 'def']
                            ]
                        ]
                    ],
                    [
                        '#a^2',
                        'Term:^2>Hashtag>>Word:a',
                        ['HASHTAG' => 1],
                        [
                            'HASHTAG' => [
                                ['value' => 'a', 'boost' => 2]
                            ]
                        ]
                    ],
                    [
                        'a#b',
                        'Or>Word:a>Hashtag>>Word:b',
                        ['WORD' => 1, 'HASHTAG' => 1],
                        [
                            'HASHTAG' => [
                                ['value' => 'b']
                            ],
                            'WORD' => [
                                ['value' => 'a']
                            ]
                        ]
                    ],
                    [
                        '"abc""def""ghi',
                        'Or>Phrase:abc>Phrase:def>Word:ghi',
                        ['PHRASE' => 2, 'WORD' => 1],
                        [
                            'PHRASE' => [
                                ['value' => 'abc'],
                                ['value' => 'def']
                            ],
                            'WORD' => [
                                ['value' => 'ghi']
                            ]
                        ]
                    ],
                    [
                        '"abc"def',
                        'Or>Phrase:abc>Word:def',
                        ['PHRASE' => 1, 'WORD' => 1],
                        [
                            'PHRASE' => [
                                ['value' => 'abc']
                            ],
                            'WORD' => [
                                ['value' => 'def']
                            ]
                        ]
                    ],
                    [
                        '"abc"def"',
                        'Or>Phrase:abc>Word:def',
                        ['PHRASE' => 1, 'WORD' => 1],
                        [
                            'PHRASE' => [
                                ['value' => 'abc']
                            ],
                            'WORD' => [
                                ['value' => 'def']
                            ]
                        ]
                    ],
                    [
                        'abc"def',
                        'Or>Word:abc>Word:def',
                        ['WORD' => 2],
                        [
                            'WORD' => [
                                ['value' => 'abc'],
                                ['value' => 'def']
                            ]
                        ]
                    ],
                    [
                        'abc"def ghi"@j"@k l',
                        'Or>Word:abc>Phrase:defghi>Mention>>Word:j>Mention>>Word:k>Word:l',
                        ['WORD' => 2, 'PHRASE' => 1, 'MENTION' => 2],
                        [
                            'MENTION' => [
                                ['value' => 'j'],
                                ['value' => 'k']
                            ],
                            'PHRASE' => [
                                ['value' => 'def ghi']
                            ],
                            'WORD' => [
                                ['value' => 'abc'],
                                ['value' => 'l']
                            ]
                        ]
                    ],
                    [
                        '#a#b@c @d#e',
                        'Or>Hashtag>>Word:a>Hashtag>>Word:b>Mention>>Word:c>Mention>>Word:d>Hashtag>>Word:e',
                        ['HASHTAG' => 3, 'MENTION' => 2],
                        [
                            'HASHTAG' => [
                                ['value' => 'a'],
                                ['value' => 'b'],
                                ['value' => 'e']
                            ],
                            'MENTION' => [
                                ['value' => 'c'],
                                ['value' => 'd']
                            ]
                        ]
                    ],
                    [
                        '(a b)^2',
                        'Or>Word:a>Term:b^2',
                        ['WORD' => 2],
                        [
                            'WORD' => [
                                ['value' => 'a'],
                                ['value' => 'b', 'boost' => 2]
                            ]
                        ]
                    ],
                    [
                        '+(a b c)-(d e f)^2',
                        'Or>IncludeTerm>>Word:a>Word:b>Word:c>Word:d>Word:e>Term:f^2',
                        ['WORD' => 6],
                        [
                            'WORD' => [
                                ['value' => 'a', 'include' => true],
                                ['value' => 'b'],
                                ['value' => 'c'],
                                ['value' => 'd'],
                                ['value' => 'e'],
                                ['value' => 'f', 'boost' => 2]
                            ]
                        ]
                    ],
                    [
                        'a b:',
                        'Or>Word:a>Word:b:',
                        ['WORD' => 2],
                        [
                            'WORD' => [
                                ['value' => 'a'],
                                ['value' => 'b:']
                            ]
                        ]
                    ],
                    [
                        'http://a.com a:>500',
                        'Or>Url:http://a.com>Term:a:>500',
                        ['URL' => 1, 'FILTER' => 1],
                        [
                            'FILTER' => [
                                ['field' => 'a', 'value' => '500', 'operator' => ':>']
                            ],
                            'URL' => [
                                ['value' => 'http://a.com']
                            ]
                        ]
                    ],
                    [
                        'a (b/c d)^2 Father and Daughter',
                        'Or>Word:a>Word:b/c>Term:d^2>Word:Father>Word:and>Word:Daughter',
                        ['WORD' => 6],
                        [
                            'WORD' => [
                                ['value' => 'a'],
                                ['value' => 'b/c'],
                                ['value' => 'd', 'boost' => 2],
                                ['value' => 'Father'],
                                ['value' => 'and'],
                                ['value' => 'Daughter']
                            ]
                        ]
                    ],
                    [
                        'a:>b^2abc',
                        'Or>Term:^2>>Term:a:>b>Word:abc',
                        ['FILTER' => 1, 'WORD' => 1],
                        [
                            'FILTER' => [
                                ['field' => 'a', 'value' => 'b', 'operator' => ':>', 'boost' => 2]
                            ],
                            'WORD' => [
                                ['value' => 'abc']
                            ]
                        ]
                    ],
                    [
                        'a + b',
                        'Or>Word:a>Word:b',
                        ['WORD' => 2],
                        [
                          'WORD' => [
                              ['value' => 'a'],
                              ['value' => 'b']
                          ]
                        ]
                    ],
                    [
                        '+(a:>b)-c:>d -e:<f',
                        'Or>IncludeTerm>>Term:a:>b>Term:c:>d>ExcludeTerm>>Term:e:<f',
                        ['FILTER' => 3],
                        [
                            'FILTER' => [
                                ['field' => 'a', 'value' => 'b', 'operator' => ':>', 'include' => true],
                                ['field' => 'c', 'value' => 'd', 'operator' => ':>'],
                                ['field' => 'e', 'value' => 'f', 'operator' => ':<', 'exclude' => true]
                            ]
                        ]
                    ],
                    [
                        '#cats #cats #cats',
                        'Or>Hashtag>>Word:cats',
                        ['HASHTAG' => 1],
                        [
                            'HASHTAG' => [
                                ['value' => 'cats']
                            ]
                        ]
                    ],
                    [
                        'http://www.google.com/#lol',
                        'Url:http://www.google.com/#lol',
                        ['URL' => 1],
                        [
                            'URL' => [
                                ['value' => 'http://www.google.com/#lol']
                            ]
                        ]
                    ],
                    [
                        'http://www.google.com/?q=a+b+c+#lol',
                        'Url:http://www.google.com/?q=a+b+c+#lol',
                        ['URL' => 1],
                        [
                            'URL' => [
                                ['value' => 'http://www.google.com/?q=a+b+c+#lol']
                            ]
                        ]
                    ],
                    [
                        'http://www.google.com/?q=a-c&s=a+@mention',
                        'Url:http://www.google.com/?q=a-c&s=a+@mention',
                        ['URL' => 1],
                        [
                            'URL' => [
                                ['value' => 'http://www.google.com/?q=a-c&s=a+@mention']
                            ]
                        ]
                    ],
                    [
                        'http://warnerbros.112.2o7.net/b/ss/wbrostoofab/1/JS-1.5.1/s72034063232131?AQB=1&ndh=1&pf=1&t=22%2F9%2F2015%2016%3A1%3A39%204%20420&fid=64F69D01980887BB-1E04C009EEE2A59C&ce=UTF-8&ns=warnerbros&cdp=3&pageName=home%3Acollection%3A%3Ahome&g=http%3A%2F%2Ftoofab.com%2F&cc=USD&events=event6&c1=Toofab.us&v1=Toofab.us&c2=collection&v2=collection&c3=home&v3=home&c15=4%3A01PM&v15=4%3A01PM&c16=Thursday&v16=Thursday&c17=Weekday&v17=Weekday&c18=%2F&v18=%2F&c19=home%3Acollection%3A%3Ahome&v19=home%3Acollection%3A%3Ahome&c27=Repeat&v27=Repeat&c59=home&v59=home&s=1920x1080&c=24&j=1.6&v=Y&k=Y&bw=1552&bh=517&AQE=1',
                        'Url:http://warnerbros.112.2o7.net/b/ss/wbrostoofab/1/JS-1.5.1/s72034063232131?AQB=1&ndh=1&pf=1&t=22%2F9%2F2015%2016%3A1%3A39%204%20420&fid=64F69D01980887BB-1E04C009EEE2A59C&ce=UTF-8&ns=warnerbros&cdp=3&pageName=home%3Acollection%3A%3Ahome&g=http%3A%2F%2Ftoofab.com%2F&cc=USD&events=event6&c1=Toofab.us&v1=Toofab.us&c2=collection&v2=collection&c3=home&v3=home&c15=4%3A01PM&v15=4%3A01PM&c16=Thursday&v16=Thursday&c17=Weekday&v17=Weekday&c18=%2F&v18=%2F&c19=home%3Acollection%3A%3Ahome&v19=home%3Acollection%3A%3Ahome&c27=Repeat&v27=Repeat&c59=home&v59=home&s=1920x1080&c=24&j=1.6&v=Y&k=Y&bw=1552&bh=517&AQE=1',
                        ['URL' => 1],
                        [
                            'URL' => [
                                ['value' => 'http://warnerbros.112.2o7.net/b/ss/wbrostoofab/1/JS-1.5.1/s72034063232131?AQB=1&ndh=1&pf=1&t=22%2F9%2F2015%2016%3A1%3A39%204%20420&fid=64F69D01980887BB-1E04C009EEE2A59C&ce=UTF-8&ns=warnerbros&cdp=3&pageName=home%3Acollection%3A%3Ahome&g=http%3A%2F%2Ftoofab.com%2F&cc=USD&events=event6&c1=Toofab.us&v1=Toofab.us&c2=collection&v2=collection&c3=home&v3=home&c15=4%3A01PM&v15=4%3A01PM&c16=Thursday&v16=Thursday&c17=Weekday&v17=Weekday&c18=%2F&v18=%2F&c19=home%3Acollection%3A%3Ahome&v19=home%3Acollection%3A%3Ahome&c27=Repeat&v27=Repeat&c59=home&v59=home&s=1920x1080&c=24&j=1.6&v=Y&k=Y&bw=1552&bh=517&AQE=1']
                            ]
                        ]
                    ],
        ];
    }

    public function testParseTextWithUnclosedQuotes()
    {
        $this->parser->readString('"phrase');
        $query = $this->parser->parse();

        $this->assertInstanceOf('Gdbots\QueryParser\Node\Word', $query);
        $this->assertEquals('phrase', $query->getToken());
        $this->assertEquals(QueryScanner::T_WORD, $query->getTokenType());
    }

    public function testParseMultiHashtags()
    {
        $this->parser->readString('#one #two #three');
        $query = $this->parser->parse();

        $output = " Or
> Hashtag
>> Word: one
> Hashtag
>> Word: two
> Hashtag
>> Word: three
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseDuplicateHashtags()
    {
        $this->parser->readString('##phrase');
        $query = $this->parser->parse();

        $output = " Hashtag
> Word: phrase
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseFilterWithBoost()
    {
        $this->parser->readString('table.fieldName:value^123');
        $query = $this->parser->parse();

        $output = " Term: ^ 123
> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQuery()
    {
        $this->parser->readString('(("phrase" #phrase) table.fieldName:value)^123');
        $query = $this->parser->parse();

        $output = " Subexpression
> Or
>> Subexpression
>>> Or
>>>> Phrase: phrase
>>>> Hashtag
>>>>> Word: phrase
>> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQueryUsingOperator()
    {
        $this->parser->readString('(("phrase" OR #phrase) AND table.fieldName:value)^123');
        $query = $this->parser->parse();

        $output = " Subexpression
> And
>> Subexpression
>>> Or
>>>> Phrase: phrase
>>>> Hashtag
>>>>> Word: phrase
>> Term: table.fieldName : value
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseComplexQueryWithIgnoreOperator()
    {
        $this->parser->readString('(("phrase" OR #phrase) AND table.fieldName:value)^123', true);
        $query = $this->parser->parse();

        $output = " Or
> Phrase: phrase
> Hashtag
>> Word: phrase
> Term: table.fieldName : value
> Term: ^ 123
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseEmoji()
    {
        $this->parser->readString('#emoji 💩 AND 🍦 OR 😳');
        $query = $this->parser->parse();

        $output = " And
> Or
>> Hashtag
>>> Word: emoji
>> Phrase: &#x1f4a9;
> Or
>> Phrase: &#x1f366;
>> Phrase: &#x1f633;
";

        $this->assertEquals($output, $this->getPrintContent($query));
    }

    public function testParseGetHashtagQueryItems()
    {
        $this->parser->readString('(("phrase" OR #phrase) AND table.fieldName:value) #boost');
        $query = $this->parser->parse();

        $hasttags = $query->getQueryItemsByTokenType(\Gdbots\QueryParser\Parser\QueryScanner::T_HASHTAG);

        $this->assertEquals(2, count($hasttags));
        $this->assertEquals('phrase', $hasttags[0]->getExpression()->getToken());
        $this->assertEquals('boost', $hasttags[1]->getExpression()->getToken());
    }

    /**
     * @return string
     */
    private function getPrintContent(Node\QueryItem $query)
    {
        ob_start();

        $query->accept($this->printer);

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
}
