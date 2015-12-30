<?php

namespace Gdbots\Tests\QueryParser;

use Gdbots\QueryParser\Tokenizer as T;

class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    /** @var T */
    protected $tokenizer;

    public function setUp()
    {
        $this->tokenizer = new T();
    }

    public function testOnlyWhitespace()
    {
        $this->tokenizer->scan('      ');
        $this->assertSame([], $this->tokenizer->getTokens());
    }

    /**
     * @dataProvider samples
     *
     * @param string $name
     * @param string $input
     * @param array $expected
     */
    public function testSamples($name, $input, array $expected)
    {
        // convert the sample 'expected' into token format.
        foreach ($expected as $k => $v) {
            if (!is_array($v)) {
                $expected[$k] = ['type' => $v, 'value' => null];
                continue;
            }

            $expected[$k] = ['type' => $v[0], 'value' => $v[1]];
        }

        $this->tokenizer->scan($input);
        $this->assertSame($expected, $this->tokenizer->getTokens(), "Sample [{$name}] with input [{$input}] failed.");
    }

    /**
     * @return array
     */
    public function samples()
    {
        return [
            /*
             * START: URLS
             */
            [
                'name' => 'url',
                'input' => 'http://test.com/1_2.html?a=b%20&c=1+2#test',
                'expected' => [
                    [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
                ]
            ],

            [
                'name' => 'required url',
                'input' => '+http://test.com/1_2.html?a=b%20&c=1+2#test',
                'expected' => [
                    T::T_REQUIRED,
                    [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
                ]
            ],

            [
                'name' => 'prohibited url',
                'input' => '-http://test.com/1_2.html?a=b%20&c=1+2#test',
                'expected' => [
                    T::T_PROHIBITED,
                    [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
                ]
            ],

            [
                'name' => 'url with boost int',
                'input' => 'http://test.com/1_2.html?a=b%20&c=1+2#test^5',
                'expected' => [
                    [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
                    T::T_BOOST,
                    [T::T_NUMBER, 5.0],
                ]
            ],

            [
                'name' => 'url with boost float',
                'input' => 'http://test.com/1_2.html?a=b%20&c=1+2#test^5.5',
                'expected' => [
                    [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
                    T::T_BOOST,
                    [T::T_NUMBER, 5.5],
                ]
            ],

            [
                'name' => 'url with fuzzy int',
                'input' => 'http://test.com/1_2.html?a=b%20&c=1+2#test~5',
                'expected' => [
                    [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
                    T::T_FUZZY,
                    [T::T_NUMBER, 5.0],
                ]
            ],

            [
                'name' => 'url with fuzzy float',
                'input' => 'http://test.com/1_2.html?a=b%20&c=1+2#test~5.5',
                'expected' => [
                    [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
                    T::T_FUZZY,
                    [T::T_NUMBER, 5.5],
                ]
            ],
            /*
             * END: URLS
             */



            /*
             * START: EMOTICONS
             * todo: need more emoticon tests
             */
            [
                'name' => 'simple emoticons',
                'input' => ':) :(',
                'expected' => [
                    [T::T_EMOTICON, ':)'],
                    [T::T_EMOTICON, ':('],
                ]
            ],
            /*
             * END: EMOTICONS
             */



            /*
             * START: EMOJIS
             */
            [
                'name' => 'simple emoji',
                'input' => 'ice 🍦 poop 💩 doh 😳',
                'expected' => [
                    [T::T_WORD, 'ice'],
                    [T::T_EMOJI, '🍦'],
                    [T::T_WORD, 'poop'],
                    [T::T_EMOJI, '💩'],
                    [T::T_WORD, 'doh'],
                    [T::T_EMOJI, '😳'],
                ]
            ],
            /*
             * END: EMOJIS
             */



            /*
             * START: PHRASES
             */
            [
                'name' => 'simple phrase',
                'input' => 'a "simple phrase"',
                'expected' => [
                    [T::T_WORD, 'a'],
                    [T::T_PHRASE, 'simple phrase'],
                ]
            ],

            [
                'name' => 'required phrase',
                'input' => 'a +"simple phrase"',
                'expected' => [
                    [T::T_WORD, 'a'],
                    T::T_REQUIRED,
                    [T::T_PHRASE, 'simple phrase'],
                ]
            ],

            [
                'name' => 'prohibited phrase',
                'input' => 'a -"simple phrase"',
                'expected' => [
                    [T::T_WORD, 'a'],
                    T::T_PROHIBITED,
                    [T::T_PHRASE, 'simple phrase'],
                ]
            ],

            [
                'name' => 'boosted phrase int',
                'input' => 'a "simple phrase"^1',
                'expected' => [
                    [T::T_WORD, 'a'],
                    [T::T_PHRASE, 'simple phrase'],
                    T::T_BOOST,
                    [T::T_NUMBER, 1.0],
                ]
            ],

            [
                'name' => 'boosted phrase float',
                'input' => 'a "simple phrase"^0.1',
                'expected' => [
                    [T::T_WORD, 'a'],
                    [T::T_PHRASE, 'simple phrase'],
                    T::T_BOOST,
                    [T::T_NUMBER, 0.1],
                ]
            ],

            [
                'name' => 'fuzzy phrase int',
                'input' => 'a "simple phrase"~1',
                'expected' => [
                    [T::T_WORD, 'a'],
                    [T::T_PHRASE, 'simple phrase'],
                    T::T_FUZZY,
                    [T::T_NUMBER, 1.0],
                ]
            ],

            [
                'name' => 'fuzzy phrase float',
                'input' => 'a "simple phrase"~0.1',
                'expected' => [
                    [T::T_WORD, 'a'],
                    [T::T_PHRASE, 'simple phrase'],
                    T::T_FUZZY,
                    [T::T_NUMBER, 0.1],
                ]
            ],

            [
                'name' => 'phrase with embedded emoticons',
                'input' => '"a smiley :)"',
                'expected' => [
                    [T::T_PHRASE, 'a smiley :)'],
                ]
            ],

            [
                'name' => 'phrase with embedded emojis',
                'input' => '"ice cream 🍦"',
                'expected' => [
                    [T::T_PHRASE, 'ice cream 🍦'],
                ]
            ],

            [
                'name' => 'phrase with embedded punctation, boosting, etc.',
                'input' => '"boosted^51.50 .. field:test~5"',
                'expected' => [
                    [T::T_PHRASE, 'boosted^51.50 .. field:test~5'],
                ]
            ],

            [
                'name' => 'phrase with dates',
                'input' => '"in the year >=2000-01-01"',
                'expected' => [
                    [T::T_PHRASE, 'in the year >=2000-01-01'],
                ]
            ],
            /*
             * END: PHRASES
             */



            /*
             * START: HASHTAGS
             */
            [
                'name' => 'simple hashtags',
                'input' => 'a #Cat in a #hat',
                'expected' => [
                    [T::T_WORD, 'a'],
                    [T::T_HASHTAG, 'Cat'],
                    [T::T_WORD, 'in'],
                    [T::T_WORD, 'a'],
                    [T::T_HASHTAG, 'hat'],
                ]
            ],

            [
                'name' => 'required/prohibited hashtags with boost',
                'input' => '+#Cat -#hat^100',
                'expected' => [
                    T::T_REQUIRED,
                    [T::T_HASHTAG, 'Cat'],
                    T::T_PROHIBITED,
                    [T::T_HASHTAG, 'hat'],
                    T::T_BOOST,
                    [T::T_NUMBER, 100.0],
                ]
            ],

            [
                'name' => 'required/prohibited hashtags with fuzzy',
                'input' => '#hat~100 #hat~100.1',
                'expected' => [
                    [T::T_HASHTAG, 'hat'],
                    T::T_FUZZY,
                    [T::T_NUMBER, 100.0],
                    [T::T_HASHTAG, 'hat'],
                    T::T_FUZZY,
                    [T::T_NUMBER, 100.1],
                ]
            ],

            [
                'name' => 'required/prohibited hashtags with boost',
                'input' => '+#Cat -#hat^100',
                'expected' => [
                    T::T_REQUIRED,
                    [T::T_HASHTAG, 'Cat'],
                    T::T_PROHIBITED,
                    [T::T_HASHTAG, 'hat'],
                    T::T_BOOST,
                    [T::T_NUMBER, 100.0],
                ]
            ],

            // todo: should we refactor to catch #hashtag#hashtag or @mention#tag or #tag@mention?
            [
                'name' => 'hashtag on hashtag',
                'input' => '#cat#cat',
                'expected' => [
                    [T::T_WORD, 'cat#cat'],
                ]
            ],
            /*
             * END: HASHTAGS
             */



            /*
             * START: MENTIONS
             */
            [
                'name' => 'simple mentions',
                'input' => '@user @user_name @user.name @user-name',
                'expected' => [
                    [T::T_MENTION, 'user'],
                    [T::T_MENTION, 'user_name'],
                    [T::T_MENTION, 'user.name'],
                    [T::T_MENTION, 'user-name'],
                ]
            ],

            [
                'name' => 'required mentions',
                'input' => '+@user +@user_name +@user.name +@user-name',
                'expected' => [
                    T::T_REQUIRED,
                    [T::T_MENTION, 'user'],
                    T::T_REQUIRED,
                    [T::T_MENTION, 'user_name'],
                    T::T_REQUIRED,
                    [T::T_MENTION, 'user.name'],
                    T::T_REQUIRED,
                    [T::T_MENTION, 'user-name'],
                ]
            ],

            [
                'name' => 'prohibited mentions',
                'input' => '-@user -@user_name -@user.name -@user-name',
                'expected' => [
                    T::T_PROHIBITED,
                    [T::T_MENTION, 'user'],
                    T::T_PROHIBITED,
                    [T::T_MENTION, 'user_name'],
                    T::T_PROHIBITED,
                    [T::T_MENTION, 'user.name'],
                    T::T_PROHIBITED,
                    [T::T_MENTION, 'user-name'],
                ]
            ],

            [
                'name' => 'mentions with emails and hashtags',
                'input' => '@john@doe.com @john#doe',
                'expected' => [
                    [T::T_WORD, 'john@doe.com'],
                    [T::T_WORD, 'john#doe'],
                ]
            ],

            [
                'name' => 'mentions with punctuation',
                'input' => '@john. @wtf! @who?',
                'expected' => [
                    [T::T_MENTION, 'john'],
                    [T::T_MENTION, 'wtf'],
                    [T::T_MENTION, 'who'],
                ]
            ],

            [
                'name' => 'mentions with special chars',
                'input' => '@john^doe @john!doe',
                'expected' => [
                    [T::T_MENTION, 'john'],
                    T::T_BOOST,
                    [T::T_WORD, 'doe'],
                    [T::T_WORD, 'john!doe'],
                ]
            ],
            /*
             * END: MENTIONS
             */



            /*
             * START: NUMBERS
             */
            [
                'name' => 'integers, decimals and exponential form',
                'input' => '100 3.1415926535898 2.2E-5',
                'expected' => [
                    [T::T_NUMBER, 100.0],
                    [T::T_NUMBER, 3.1415926535898],
                    [T::T_NUMBER, 2.2E-5],
                ]
            ],

            [
                'name' => 'negative integers, decimals and exponential form',
                'input' => '-100 -3.1415926535898 -2.2E-5',
                'expected' => [
                    [T::T_NUMBER, -100.0],
                    [T::T_NUMBER, -3.1415926535898],
                    [T::T_NUMBER, -2.2E-5],
                ]
            ],

            [
                'name' => 'words with boosted numbers',
                'input' => 'word^100 word^3.1415926535898 word^2.2E-5',
                'expected' => [
                    [T::T_WORD, 'word'],
                    T::T_BOOST,
                    [T::T_NUMBER, 100.0],
                    [T::T_WORD, 'word'],
                    T::T_BOOST,
                    [T::T_NUMBER, 3.1415926535898],
                    [T::T_WORD, 'word'],
                    T::T_BOOST,
                    [T::T_NUMBER, 2.2E-5],
                ]
            ],

            [
                'name' => 'words with boosted negative numbers',
                'input' => 'word^-100 word^-3.1415926535898 word^-2.2E-5',
                'expected' => [
                    [T::T_WORD, 'word'],
                    T::T_BOOST,
                    [T::T_NUMBER, -100.0],
                    [T::T_WORD, 'word'],
                    T::T_BOOST,
                    [T::T_NUMBER, -3.1415926535898],
                    [T::T_WORD, 'word'],
                    T::T_BOOST,
                    [T::T_NUMBER, -2.2E-5],
                ]
            ],

            [
                'name' => 'words with fuzzy numbers',
                'input' => 'word~100 word~3.1415926535898 word~2.2E-5',
                'expected' => [
                    [T::T_WORD, 'word'],
                    T::T_FUZZY,
                    [T::T_NUMBER, 100.0],
                    [T::T_WORD, 'word'],
                    T::T_FUZZY,
                    [T::T_NUMBER, 3.1415926535898],
                    [T::T_WORD, 'word'],
                    T::T_FUZZY,
                    [T::T_NUMBER, 2.2E-5],
                ]
            ],

            [
                'name' => 'words with fuzzy negative numbers',
                'input' => 'word~-100 word~-3.1415926535898 word~-2.2E-5',
                'expected' => [
                    [T::T_WORD, 'word'],
                    T::T_FUZZY,
                    [T::T_NUMBER, -100.0],
                    [T::T_WORD, 'word'],
                    T::T_FUZZY,
                    [T::T_NUMBER, -3.1415926535898],
                    [T::T_WORD, 'word'],
                    T::T_FUZZY,
                    [T::T_NUMBER, -2.2E-5],
                ]
            ],
            /*
             * END: NUMBERS
             */



            /*
             * START: FIELDS
             */
            [
                'name' => 'fields with hypen, underscore and dot',
                'input' => '+first-name:homer -last_name:simpson job.performance:poor^5',
                'expected' => [
                    T::T_REQUIRED,
                    [T::T_FILTER_START, 'first-name'],
                    [T::T_WORD, 'homer'],
                    T::T_FILTER_END,
                    T::T_PROHIBITED,
                    [T::T_FILTER_START, 'last_name'],
                    [T::T_WORD, 'simpson'],
                    T::T_FILTER_END,
                    [T::T_FILTER_START, 'job.performance'],
                    [T::T_WORD, 'poor'],
                    T::T_BOOST,
                    [T::T_NUMBER, 5.0],
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field with field in it',
                'input' => 'field:subfield:what',
                'expected' => [
                    [T::T_FILTER_START, 'field'],
                    [T::T_WORD, 'subfield:what'],
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field with no value',
                'input' => 'field:',
                'expected' => [
                    [T::T_FILTER_START, 'field'],
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field with phrases',
                'input' => 'field:"boosted^5 +required"^1 -field:"[1..5]"~4',
                'expected' => [
                    [T::T_FILTER_START, 'field'],
                    [T::T_PHRASE, 'boosted^5 +required'],
                    T::T_BOOST,
                    [T::T_NUMBER, 1.0],
                    T::T_FILTER_END,
                    T::T_PROHIBITED,
                    [T::T_FILTER_START, 'field'],
                    [T::T_PHRASE, '[1..5]'],
                    T::T_FUZZY,
                    [T::T_NUMBER, 4.0],
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field with greater/less than',
                'input' => 'field:>100 field:>=100.1 field:<100 field:<=100.1',
                'expected' => [
                    [T::T_FILTER_START, 'field'],
                    T::T_GREATER_THAN,
                    [T::T_NUMBER, 100.0],
                    T::T_FILTER_END,
                    [T::T_FILTER_START, 'field'],
                    T::T_GREATER_THAN,
                    T::T_EQUALS,
                    [T::T_NUMBER, 100.1],
                    T::T_FILTER_END,
                    [T::T_FILTER_START, 'field'],
                    T::T_LESS_THAN,
                    [T::T_NUMBER, 100.0],
                    T::T_FILTER_END,
                    [T::T_FILTER_START, 'field'],
                    T::T_LESS_THAN,
                    T::T_EQUALS,
                    [T::T_NUMBER, 100.1],
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field with a hashtag or mention',
                'input' => 'field:#cats field:@user.name',
                'expected' => [
                    [T::T_FILTER_START, 'field'],
                    [T::T_HASHTAG, 'cats'],
                    T::T_FILTER_END,
                    [T::T_FILTER_START, 'field'],
                    [T::T_MENTION, 'user.name'],
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field with inclusive range',
                'input' => 'field:[1..5] +field:[1 TO 5]',
                'expected' => [
                    [T::T_FILTER_START, 'field'],
                    T::T_RANGE_INCL_START,
                    [T::T_NUMBER, 1.0],
                    T::T_TO,
                    [T::T_NUMBER, 5.0],
                    T::T_RANGE_INCL_END,
                    T::T_FILTER_END,
                    T::T_REQUIRED,
                    [T::T_FILTER_START, 'field'],
                    T::T_RANGE_INCL_START,
                    [T::T_NUMBER, 1.0],
                    T::T_TO,
                    [T::T_NUMBER, 5.0],
                    T::T_RANGE_INCL_END,
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field with exclusive range',
                'input' => 'field:{1.1..5.5} +field:{1.1 TO 5.5}',
                'expected' => [
                    [T::T_FILTER_START, 'field'],
                    T::T_RANGE_EXCL_START,
                    [T::T_NUMBER, 1.1],
                    T::T_TO,
                    [T::T_NUMBER, 5.5],
                    T::T_RANGE_EXCL_END,
                    T::T_FILTER_END,
                    T::T_REQUIRED,
                    [T::T_FILTER_START, 'field'],
                    T::T_RANGE_EXCL_START,
                    [T::T_NUMBER, 1.1],
                    T::T_TO,
                    [T::T_NUMBER, 5.5],
                    T::T_RANGE_EXCL_END,
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field with subquery',
                'input' => 'field:(cat or dog) test',
                'expected' => [
                    [T::T_FILTER_START, 'field'],
                    T::T_SUBQUERY_START,
                    [T::T_WORD, 'cat'],
                    T::T_OR,
                    [T::T_WORD, 'dog'],
                    T::T_SUBQUERY_END,
                    T::T_FILTER_END,
                    [T::T_WORD, 'test'],
                ]
            ],

            [
                'name' => 'field with range in subquery',
                'input' => 'field:(cat or 1..5)',
                'expected' => [
                    [T::T_FILTER_START, 'field'],
                    T::T_SUBQUERY_START,
                    [T::T_WORD, 'cat'],
                    T::T_OR,
                    [T::T_NUMBER, 1.0],
                    [T::T_NUMBER, 5.0],
                    T::T_SUBQUERY_END,
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field with dates',
                'input' => 'field:2015-12-18 field:>2015-12-18 field:<2015-12-18 field:>=2015-12-18 field:<=2015-12-18',
                'expected' => [
                    [T::T_FILTER_START, 'field'],
                    [T::T_DATE, '2015-12-18'],
                    T::T_FILTER_END,
                    [T::T_FILTER_START, 'field'],
                    T::T_GREATER_THAN,
                    [T::T_DATE, '2015-12-18'],
                    T::T_FILTER_END,
                    [T::T_FILTER_START, 'field'],
                    T::T_LESS_THAN,
                    [T::T_DATE, '2015-12-18'],
                    T::T_FILTER_END,
                    [T::T_FILTER_START, 'field'],
                    T::T_GREATER_THAN,
                    T::T_EQUALS,
                    [T::T_DATE, '2015-12-18'],
                    T::T_FILTER_END,
                    [T::T_FILTER_START, 'field'],
                    T::T_LESS_THAN,
                    T::T_EQUALS,
                    [T::T_DATE, '2015-12-18'],
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field leading _ and uuid',
                'input' => '_id:a9fc3e46-150a-45cd-ad39-c80f93119900^5',
                'expected' => [
                    [T::T_FILTER_START, '_id'],
                    [T::T_WORD, 'a9fc3e46-150a-45cd-ad39-c80f93119900'],
                    T::T_BOOST,
                    [T::T_NUMBER, 5.0],
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field with mentions and emails',
                'input' => 'email:john@doe.com -user:@twitterz',
                'expected' => [
                    [T::T_FILTER_START, 'email'],
                    [T::T_WORD, 'john@doe.com'],
                    T::T_FILTER_END,
                    T::T_PROHIBITED,
                    [T::T_FILTER_START, 'user'],
                    [T::T_MENTION, 'twitterz'],
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'field with hashtags',
                'input' => 'tags:#cats tags:(#cats || #dogs)',
                'expected' => [
                    [T::T_FILTER_START, 'tags'],
                    [T::T_HASHTAG, 'cats'],
                    T::T_FILTER_END,
                    [T::T_FILTER_START, 'tags'],
                    T::T_SUBQUERY_START,
                    [T::T_HASHTAG, 'cats'],
                    T::T_OR,
                    [T::T_HASHTAG, 'dogs'],
                    T::T_SUBQUERY_END,
                    T::T_FILTER_END,
                ]
            ],
            /*
             * END: FIELDS
             */



            /*
             * START: WORDS
             */
            [
                'name' => 'word with hashtag or mention in it',
                'input' => 'omg#lol omg@user @mention#tag #tag@mention',
                'expected' => [
                    [T::T_WORD, 'omg#lol'],
                    [T::T_WORD, 'omg@user'],
                    [T::T_WORD, 'mention#tag'],
                    [T::T_WORD, 'tag@mention'],
                ]
            ],

            [
                'name' => 'required/prohibited words',
                'input' => '+c.h.u.d. -zombieland +ac/dc^5',
                'expected' => [
                    T::T_REQUIRED,
                    [T::T_WORD, 'c.h.u.d'],
                    T::T_PROHIBITED,
                    [T::T_WORD, 'zombieland'],
                    T::T_REQUIRED,
                    [T::T_WORD, 'ac/dc'],
                    T::T_BOOST,
                    [T::T_NUMBER, 5.0],
                ]
            ],

            [
                'name' => 'words that have embedded operators',
                'input' => 'candy and oreos || dandy && chores^5',
                'expected' => [
                    [T::T_WORD, 'candy'],
                    T::T_AND,
                    [T::T_WORD, 'oreos'],
                    T::T_OR,
                    [T::T_WORD, 'dandy'],
                    T::T_AND,
                    [T::T_WORD, 'chores'],
                    T::T_BOOST,
                    [T::T_NUMBER, 5.0],
                ]
            ],
            /*
             * END: WORDS
             */



            /*
             * START: DATES
             */
            [
                'name' => 'dates in string',
                'input' => '2000-01-01 >=2000-01-01 (+2015-12-18) -2015-12-18',
                'expected' => [
                    [T::T_DATE, '2000-01-01'],
                    [T::T_DATE, '2000-01-01'],
                    T::T_SUBQUERY_START,
                    T::T_REQUIRED,
                    [T::T_DATE, '2015-12-18'],
                    T::T_SUBQUERY_END,
                    T::T_PROHIBITED,
                    [T::T_DATE, '2015-12-18'],
                ]
            ],

            [
                'name' => 'dates on dates',
                'input' => '2000-01-012000-01-01 2000-01-01^2000-01-01',
                'expected' => [
                    [T::T_WORD, '2000-01-012000-01-01'],
                    [T::T_DATE, '2000-01-01'],
                    T::T_BOOST,
                    [T::T_DATE, '2000-01-01'],
                ]
            ],
            /*
             * END: DATES
             */



            /*
             * START: ACCENTED CHARS
             */
            [
                'name' => 'accents and hyphens',
                'input' => '+Beyoncé Giselle Knowles-Carter',
                'expected' => [
                    T::T_REQUIRED,
                    [T::T_WORD, 'Beyoncé'],
                    [T::T_WORD, 'Giselle'],
                    [T::T_WORD, 'Knowles-Carter'],
                ]
            ],

            [
                'name' => 'accents and hyphen spice',
                'input' => 'J. Lo => Emme Maribel Muñiz $p0rty-spicé',
                'expected' => [
                    [T::T_WORD, 'J'],
                    [T::T_WORD, 'Lo'],
                    [T::T_WORD, 'Emme'],
                    [T::T_WORD, 'Maribel'],
                    [T::T_WORD, 'Muñiz'],
                    [T::T_WORD, '$p0rty-spicé'],
                ]
            ],
            /*
             * END: ACCENTED CHARS
             */



            /*
             * START: RAPPERS and POP STARS
             */
            [
                'name' => 'crazy a$$ names',
                'input' => 'p!nk and K$sha in a tr33 with 50¢',
                'expected' => [
                    [T::T_WORD, 'p!nk'],
                    T::T_AND,
                    [T::T_WORD, 'K$sha'],
                    [T::T_WORD, 'in'],
                    [T::T_WORD, 'a'],
                    [T::T_WORD, 'tr33'],
                    [T::T_WORD, 'with'],
                    [T::T_WORD, '50¢'],
                ]
            ],

            [
                'name' => 'my name is math(ish)',
                'input' => '+florence+machine ac/dc^11 Stellastarr* T\'Pau ​¡Forward, Russia! "¡Forward, Russia!"~',
                'expected' => [
                    T::T_REQUIRED,
                    [T::T_WORD, 'florence+machine'],
                    [T::T_WORD, 'ac/dc'],
                    T::T_BOOST,
                    [T::T_NUMBER, 11.0],
                    [T::T_WORD, 'Stellastarr'],
                    T::T_WILDCARD,
                    [T::T_WORD, 'T\'Pau'],
                    [T::T_WORD, '​¡Forward'],
                    [T::T_WORD, 'Russia'],
                    [T::T_PHRASE, '¡Forward, Russia'],
                    T::T_FUZZY,
                ]
            ],
            /*
             * END: RAPPERS and POP STARS
             */



            /*
             * START: WEIRD QUERIES
             */
            [
                'name' => 'whip nae nae',
                'input' => 'Watch Me (Whip/Nae Nae)',
                'expected' => [
                    [T::T_WORD, 'Watch'],
                    [T::T_WORD, 'Me'],
                    T::T_SUBQUERY_START,
                    [T::T_WORD, 'Whip/Nae'],
                    [T::T_WORD, 'Nae'],
                    T::T_SUBQUERY_END,
                ]
            ],

            [
                'name' => 'use of || then and required subquery',
                'input' => 'test || AND what (+test)',
                'expected' => [
                    [T::T_WORD, 'test'],
                    T::T_OR,
                    T::T_AND,
                    [T::T_WORD, 'what'],
                    T::T_SUBQUERY_START,
                    T::T_REQUIRED,
                    [T::T_WORD, 'test'],
                    T::T_SUBQUERY_END,
                ]
            ],

            [
                'name' => 'mega subqueries, all non-sensical',
                'input' => 'test OR ( ( 1 ) OR ( ( 2 ) ) OR ( ( ( 3.14 ) ) ) OR a OR +b ) OR +field:>1',
                'expected' => [
                    [T::T_WORD, 'test'],
                    T::T_OR,
                    T::T_SUBQUERY_START,
                    [T::T_NUMBER, 1.0],
                    T::T_SUBQUERY_END,
                    T::T_OR,
                    T::T_SUBQUERY_START,
                    [T::T_NUMBER, 2.0],
                    T::T_SUBQUERY_END,
                    T::T_OR,
                    T::T_SUBQUERY_START,
                    [T::T_NUMBER, 3.14],
                    T::T_SUBQUERY_END,
                    T::T_OR,
                    [T::T_WORD, 'a'],
                    T::T_OR,
                    T::T_REQUIRED,
                    [T::T_WORD, 'b'],
                    T::T_OR,
                    T::T_REQUIRED,
                    [T::T_FILTER_START, 'field'],
                    T::T_GREATER_THAN,
                    [T::T_NUMBER, 1.0],
                    T::T_FILTER_END,
                ]
            ],

            [
                'name' => 'common dotted things',
                'input' => 'R.I.P. Motörhead',
                'expected' => [
                    [T::T_WORD, 'R.I.P'],
                    [T::T_WORD, 'Motörhead'],
                ]
            ],

            [
                'name' => 'ignored chars',
                'input' => '!!! ! $ _ . ; %',
                'expected' => []
            ],

            [
                'name' => 'elastic search example 1',
                'input' => '"john smith"^2   (foo bar)^4',
                'expected' => [
                    [T::T_PHRASE, 'john smith'],
                    T::T_BOOST,
                    [T::T_NUMBER, 2.0],
                    T::T_SUBQUERY_START,
                    [T::T_WORD, 'foo'],
                    [T::T_WORD, 'bar'],
                    T::T_SUBQUERY_END,
                    T::T_BOOST,
                    [T::T_NUMBER, 4.0],
                ]
            ],

            [
                'name' => 'intentionally mutant',
                'input' => '[blah "[[shortcode]]" akd_ -gj% ! @* (+=} --> ;\' <a onclick="javascript:alert(\'test\')>click</a>',
                'expected' => [
                    [T::T_WORD, 'blah'],
                    [T::T_PHRASE, '[[shortcode]]'],
                    [T::T_WORD, 'akd_'],
                    T::T_PROHIBITED,
                    [T::T_WORD, 'gj%'],
                    T::T_SUBQUERY_START,
                    T::T_REQUIRED,
                    T::T_PROHIBITED,
                    [T::T_WORD, 'a'],
                    [T::T_WORD, 'onclick'],
                    [T::T_WORD, 'javascript:alert'],
                    [T::T_WORD, 'test'],
                    T::T_SUBQUERY_END,
                    [T::T_WORD, 'click'],
                    [T::T_WORD, 'a'],
                ]
            ],

            [
                'name' => 'intentionally mutanter',
                'input' => '[blah &quot;[[shortcode]]&quot; akd_ -gj% ! @* (+=} --&gt; ;\' &lt;a onclick=&quot;javascript:alert(\'test\')&gt;click&lt;/a&gt;',
                'expected' => [
                    [T::T_WORD, 'blah'],
                    [T::T_WORD, 'quot'],
                    [T::T_WORD, 'shortcode'],
                    [T::T_WORD, 'quot'],
                    [T::T_WORD, 'akd_'],
                    T::T_PROHIBITED,
                    [T::T_WORD, 'gj%'],
                    T::T_SUBQUERY_START,
                    T::T_REQUIRED,
                    T::T_PROHIBITED,
                    [T::T_WORD, 'gt'],
                    [T::T_WORD, 'lt;a'],
                    [T::T_WORD, 'onclick'],
                    [T::T_WORD, 'quot;javascript:alert'],
                    [T::T_WORD, 'test'],
                    T::T_SUBQUERY_END,
                    [T::T_WORD, 'gt;click&lt;/a&gt'],
                ]
            ],

            [
                'name' => 'xss1',
                'input' => '<IMG SRC=j&#X41vascript:alert(\'test2\')>',
                'expected' => [
                    [T::T_WORD, 'IMG'],
                    [T::T_WORD, 'SRC'],
                    [T::T_WORD, 'j&#X41vascript:alert'],
                    T::T_SUBQUERY_START,
                    [T::T_WORD, 'test2'],
                    T::T_SUBQUERY_END,
                ]
            ],
            /*
             * END: WEIRD QUERIES
             */
        ];
    }
}
