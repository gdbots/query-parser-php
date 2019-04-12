<?php
declare(strict_types=1);

use Gdbots\QueryParser\Enum\BoolOperator;
use Gdbots\QueryParser\Enum\ComparisonOperator;
use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\Numbr;
use Gdbots\QueryParser\Node\NumberRange;
use Gdbots\QueryParser\Node\Phrase;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Url;
use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\Token as T;

return [
    /*
     * START: URLS
     */
    [
        'name'            => 'url',
        'input'           => 'http://test.com/1_2.html?a=b%20&c=1+2#test',
        'expected_tokens' => [
            [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
        ],
        'expected_nodes'  => [
            new Url('http://test.com/1_2.html?a=b%20&c=1+2#test'),
        ],
    ],

    [
        'name'            => 'required url',
        'input'           => '+http://test.com/1_2.html?a=b%20&c=1+2#test',
        'expected_tokens' => [
            T::T_REQUIRED,
            [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
        ],
        'expected_nodes'  => [
            new Url('http://test.com/1_2.html?a=b%20&c=1+2#test', BoolOperator::REQUIRED()),
        ],
    ],

    [
        'name'            => 'prohibited url',
        'input'           => '-http://test.com/1_2.html?a=b%20&c=1+2#test',
        'expected_tokens' => [
            T::T_PROHIBITED,
            [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
        ],
        'expected_nodes'  => [
            new Url('http://test.com/1_2.html?a=b%20&c=1+2#test', BoolOperator::PROHIBITED()),
        ],
    ],

    [
        'name'            => 'url with boost int',
        'input'           => 'http://test.com/1_2.html?a=b%20&c=1+2#test^5',
        'expected_tokens' => [
            [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
            T::T_BOOST,
            [T::T_NUMBER, 5.0],
        ],
        'expected_nodes'  => [
            new Url('http://test.com/1_2.html?a=b%20&c=1+2#test', null, true, 5.0),
        ],
    ],

    [
        'name'            => 'url with boost float',
        'input'           => 'http://test.com/1_2.html?a=b%20&c=1+2#test^15.5',
        'expected_tokens' => [
            [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
            T::T_BOOST,
            [T::T_NUMBER, 15.5],
        ],
        'expected_nodes'  => [
            new Url('http://test.com/1_2.html?a=b%20&c=1+2#test', null, true, Url::MAX_BOOST),
        ],
    ],

    [
        'name'            => 'url with fuzzy int',
        'input'           => 'http://test.com/1_2.html?a=b%20&c=1+2#test~5',
        'expected_tokens' => [
            [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
            T::T_FUZZY,
            [T::T_NUMBER, 5.0],
        ],
        'expected_nodes'  => [
            new Url('http://test.com/1_2.html?a=b%20&c=1+2#test'),
        ],
    ],

    [
        'name'            => 'url with fuzzy float',
        'input'           => 'http://test.com/1_2.html?a=b%20&c=1+2#test~5.5',
        'expected_tokens' => [
            [T::T_URL, 'http://test.com/1_2.html?a=b%20&c=1+2#test'],
            T::T_FUZZY,
            [T::T_NUMBER, 5.5],
        ],
        'expected_nodes'  => [
            new Url('http://test.com/1_2.html?a=b%20&c=1+2#test'),
        ],
    ],
    /*
     * END: URLS
     */


    /*
     * START: EMOTICONS
     * todo: need more emoticon tests
     */
    [
        'name'            => 'simple emoticons',
        'input'           => ':) :(',
        'expected_tokens' => [
            [T::T_EMOTICON, ':)'],
            [T::T_EMOTICON, ':('],
        ],
        'expected_nodes'  => [
            new Emoticon(':)', BoolOperator::REQUIRED()),
            new Emoticon(':(', BoolOperator::REQUIRED()),
        ],
    ],
    /*
     * END: EMOTICONS
     */


    /*
     * START: EMOJIS
     */
    [
        'name'            => 'simple emoji',
        'input'           => 'ice 🍦 poop 💩 doh 😳',
        'expected_tokens' => [
            [T::T_WORD, 'ice'],
            [T::T_EMOJI, '🍦'],
            [T::T_WORD, 'poop'],
            [T::T_EMOJI, '💩'],
            [T::T_WORD, 'doh'],
            [T::T_EMOJI, '😳'],
        ],
        'expected_nodes'  => [
            new Word('ice'),
            new Emoji('🍦', BoolOperator::REQUIRED()),
            new Word('poop'),
            new Emoji('💩', BoolOperator::REQUIRED()),
            new Word('doh'),
            new Emoji('😳', BoolOperator::REQUIRED()),
        ],
    ],
    /*
     * END: EMOJIS
     */


    /*
     * START: BOOST AND FUZZY
     */
    [
        'name'            => 'boost and fuzzy in filter',
        'input'           => 'f:b^5 f:f~5',
        'expected_tokens' => [
            [T::T_FIELD_START, 'f'],
            [T::T_WORD, 'b'],
            T::T_FIELD_END,
            T::T_BOOST,
            [T::T_NUMBER, 5.0],
            [T::T_FIELD_START, 'f'],
            [T::T_WORD, 'f'],
            T::T_FIELD_END,
            T::T_FUZZY,
            [T::T_NUMBER, 5.0],
        ],
        'expected_nodes'  => [
            new Field('f', new Word('b'), null, true, 5.0),
            new Field('f', new Word('f'), null, false, Field::DEFAULT_BOOST),
        ],
    ],

    [
        'name'            => 'boost and fuzzy in range',
        'input'           => 'f:[1^5..5]^5 f:[1~5..5]~5',
        'expected_tokens' => [
            [T::T_FIELD_START, 'f'],
            T::T_RANGE_INCL_START,
            [T::T_NUMBER, 1.0],
            [T::T_NUMBER, 5.0],
            T::T_TO,
            [T::T_NUMBER, 5.0],
            T::T_RANGE_INCL_END,
            T::T_FIELD_END,
            T::T_BOOST,
            [T::T_NUMBER, 5.0],
            [T::T_FIELD_START, 'f'],
            T::T_RANGE_INCL_START,
            [T::T_NUMBER, 1.0],
            [T::T_NUMBER, 5.0],
            T::T_TO,
            [T::T_NUMBER, 5.0],
            T::T_RANGE_INCL_END,
            T::T_FIELD_END,
            T::T_FUZZY,
            [T::T_NUMBER, 5.0],
        ],
        'expected_nodes'  => [
            new Field(
                'f',
                new NumberRange(
                    new Numbr(1.0),
                    new Numbr(5.0)
                ),
                null,
                true,
                5.0
            ),
            new Field(
                'f',
                new NumberRange(
                    new Numbr(1.0),
                    new Numbr(5.0)
                ),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
        ],
    ],
    /*
     * END: BOOST AND FUZZY
     */


    /*
     * START: PHRASES
     */
    [
        'name'            => 'simple phrase',
        'input'           => 'a "simple phrase"',
        'expected_tokens' => [
            [T::T_WORD, 'a'],
            [T::T_PHRASE, 'simple phrase'],
        ],
        'expected_nodes'  => [
            new Word('a'),
            new Phrase('simple phrase'),
        ],
    ],

    [
        'name'            => 'required phrase',
        'input'           => 'a +"simple phrase"',
        'expected_tokens' => [
            [T::T_WORD, 'a'],
            T::T_REQUIRED,
            [T::T_PHRASE, 'simple phrase'],
        ],
        'expected_nodes'  => [
            new Word('a'),
            new Phrase('simple phrase', BoolOperator::REQUIRED()),
        ],
    ],

    [
        'name'            => 'prohibited phrase',
        'input'           => 'a -"simple phrase"',
        'expected_tokens' => [
            [T::T_WORD, 'a'],
            T::T_PROHIBITED,
            [T::T_PHRASE, 'simple phrase'],
        ],
        'expected_nodes'  => [
            new Word('a'),
            new Phrase('simple phrase', BoolOperator::PROHIBITED()),
        ],
    ],

    [
        'name'            => 'boosted phrase int',
        'input'           => 'a "simple phrase"^1',
        'expected_tokens' => [
            [T::T_WORD, 'a'],
            [T::T_PHRASE, 'simple phrase'],
            T::T_BOOST,
            [T::T_NUMBER, 1.0],
        ],
        'expected_nodes'  => [
            new Word('a'),
            new Phrase('simple phrase', null, true, 1.0),
        ],
    ],

    [
        'name'            => 'boosted phrase float',
        'input'           => 'a "simple phrase"^0.1',
        'expected_tokens' => [
            [T::T_WORD, 'a'],
            [T::T_PHRASE, 'simple phrase'],
            T::T_BOOST,
            [T::T_NUMBER, 0.1],
        ],
        'expected_nodes'  => [
            new Word('a'),
            new Phrase('simple phrase', null, true, 0.1),
        ],
    ],

    [
        'name'            => 'fuzzy phrase int',
        'input'           => 'a "simple phrase"~1',
        'expected_tokens' => [
            [T::T_WORD, 'a'],
            [T::T_PHRASE, 'simple phrase'],
            T::T_FUZZY,
            [T::T_NUMBER, 1.0],
        ],
        'expected_nodes'  => [
            new Word('a'),
            new Phrase('simple phrase', null, false, Phrase::DEFAULT_BOOST, true, Phrase::MIN_FUZZY),
        ],
    ],

    [
        'name'            => 'fuzzy phrase float',
        'input'           => 'a "simple phrase"~0.1',
        'expected_tokens' => [
            [T::T_WORD, 'a'],
            [T::T_PHRASE, 'simple phrase'],
            T::T_FUZZY,
            [T::T_NUMBER, 0.1],
        ],
        'expected_nodes'  => [
            new Word('a'),
            new Phrase('simple phrase', null, false, Phrase::DEFAULT_BOOST, true, Phrase::MIN_FUZZY),
        ],
    ],

    [
        'name'            => 'phrase with embedded emoticons',
        'input'           => '"a smiley :)"',
        'expected_tokens' => [
            [T::T_PHRASE, 'a smiley :)'],
        ],
        'expected_nodes'  => [
            new Phrase('a smiley :)'),
        ],
    ],

    [
        'name'            => 'phrase with embedded emojis',
        'input'           => '"ice cream 🍦"',
        'expected_tokens' => [
            [T::T_PHRASE, 'ice cream 🍦'],
        ],
        'expected_nodes'  => [
            new Phrase('ice cream 🍦'),
        ],
    ],

    [
        'name'            => 'phrase with embedded punctation, boosting, etc.',
        'input'           => '"boosted^51.50 .. field:test~5"',
        'expected_tokens' => [
            [T::T_PHRASE, 'boosted^51.50 .. field:test~5'],
        ],
        'expected_nodes'  => [
            new Phrase('boosted^51.50 .. field:test~5'),
        ],
    ],

    [
        'name'            => 'phrase with dates',
        'input'           => '"in the year >=2000-01-01"',
        'expected_tokens' => [
            [T::T_PHRASE, 'in the year >=2000-01-01'],
        ],
        'expected_nodes'  => [
            new Phrase('in the year >=2000-01-01'),
        ],
    ],

    [
        'name'            => 'phrase on phrase',
        'input'           => '"p1""p2""p3',
        'expected_tokens' => [
            [T::T_PHRASE, 'p1'],
            [T::T_PHRASE, 'p2'],
            [T::T_WORD, 'p3'],
        ],
        'expected_nodes'  => [
            new Phrase('p1'),
            new Phrase('p2'),
            new Word('p3'),
        ],
    ],
    /*
     * END: PHRASES
     */


    /*
     * START: HASHTAGS
     */
    [
        'name'            => 'simple hashtags',
        'input'           => 'a #Cat in a #hat',
        'expected_tokens' => [
            [T::T_WORD, 'a'],
            [T::T_HASHTAG, 'Cat'],
            [T::T_WORD, 'in'],
            [T::T_WORD, 'a'],
            [T::T_HASHTAG, 'hat'],
        ],
        'expected_nodes'  => [
            new Word('a'),
            new Hashtag('Cat', BoolOperator::REQUIRED()),
            new Word('in'),
            new Word('a'),
            new Hashtag('hat', BoolOperator::REQUIRED()),
        ],
    ],

    [
        'name'            => 'required/prohibited hashtags with boost',
        'input'           => '+#Cat -#hat^100',
        'expected_tokens' => [
            T::T_REQUIRED,
            [T::T_HASHTAG, 'Cat'],
            T::T_PROHIBITED,
            [T::T_HASHTAG, 'hat'],
            T::T_BOOST,
            [T::T_NUMBER, 100.0],
        ],
        'expected_nodes'  => [
            new Hashtag('Cat', BoolOperator::REQUIRED()),
            new Hashtag('hat', BoolOperator::PROHIBITED(), true, Hashtag::MAX_BOOST),
        ],
    ],

    [
        'name'            => 'required/prohibited hashtags with fuzzy',
        'input'           => '#hat~100 #hat~100.1',
        'expected_tokens' => [
            [T::T_HASHTAG, 'hat'],
            T::T_FUZZY,
            [T::T_NUMBER, 100.0],
            [T::T_HASHTAG, 'hat'],
            T::T_FUZZY,
            [T::T_NUMBER, 100.1],
        ],
        'expected_nodes'  => [
            new Hashtag('hat', BoolOperator::REQUIRED()),
            new Hashtag('hat', BoolOperator::REQUIRED()),
        ],
    ],

    [
        'name'            => 'required/prohibited hashtags with boost',
        'input'           => '+#Cat -#hat^100 #_cat #2015cat__',
        'expected_tokens' => [
            T::T_REQUIRED,
            [T::T_HASHTAG, 'Cat'],
            T::T_PROHIBITED,
            [T::T_HASHTAG, 'hat'],
            T::T_BOOST,
            [T::T_NUMBER, 100.0],
            [T::T_HASHTAG, '_cat'],
            [T::T_HASHTAG, '2015cat__'],
        ],
        'expected_nodes'  => [
            new Hashtag('Cat', BoolOperator::REQUIRED()),
            new Hashtag('hat', BoolOperator::PROHIBITED(), true, Hashtag::MAX_BOOST),
            new Hashtag('_cat', BoolOperator::REQUIRED()),
            new Hashtag('2015cat__', BoolOperator::REQUIRED()),
        ],
    ],

    // todo: should we refactor to catch #hashtag#hashtag or @mention#tag or #tag@mention?
    [
        'name'            => 'hashtag on hashtag and double hashtag',
        'input'           => '#cat#cat ##cat #####cat',
        'expected_tokens' => [
            [T::T_WORD, 'cat#cat'],
            [T::T_HASHTAG, 'cat'],
            [T::T_HASHTAG, 'cat'],
        ],
        'expected_nodes'  => [
            new Word('cat#cat'),
            new Hashtag('cat', BoolOperator::REQUIRED()),
            new Hashtag('cat', BoolOperator::REQUIRED()),
        ],
    ],
    /*
     * END: HASHTAGS
     */


    /*
     * START: MENTIONS
     */
    [
        'name'            => 'simple mentions',
        'input'           => '@user @user_name @user.name @user-name',
        'expected_tokens' => [
            [T::T_MENTION, 'user'],
            [T::T_MENTION, 'user_name'],
            [T::T_MENTION, 'user.name'],
            [T::T_MENTION, 'user-name'],
        ],
        'expected_nodes'  => [
            new Mention('user', BoolOperator::REQUIRED()),
            new Mention('user_name', BoolOperator::REQUIRED()),
            new Mention('user.name', BoolOperator::REQUIRED()),
            new Mention('user-name', BoolOperator::REQUIRED()),

        ],
    ],

    [
        'name'            => 'required mentions',
        'input'           => '+@user +@user_name +@user.name +@user-name',
        'expected_tokens' => [
            T::T_REQUIRED,
            [T::T_MENTION, 'user'],
            T::T_REQUIRED,
            [T::T_MENTION, 'user_name'],
            T::T_REQUIRED,
            [T::T_MENTION, 'user.name'],
            T::T_REQUIRED,
            [T::T_MENTION, 'user-name'],
        ],
        'expected_nodes'  => [
            new Mention('user', BoolOperator::REQUIRED()),
            new Mention('user_name', BoolOperator::REQUIRED()),
            new Mention('user.name', BoolOperator::REQUIRED()),
            new Mention('user-name', BoolOperator::REQUIRED()),
        ],
    ],

    [
        'name'            => 'prohibited mentions',
        'input'           => '-@user -@user_name -@user.name -@user-name',
        'expected_tokens' => [
            T::T_PROHIBITED,
            [T::T_MENTION, 'user'],
            T::T_PROHIBITED,
            [T::T_MENTION, 'user_name'],
            T::T_PROHIBITED,
            [T::T_MENTION, 'user.name'],
            T::T_PROHIBITED,
            [T::T_MENTION, 'user-name'],
        ],
        'expected_nodes'  => [
            new Mention('user', BoolOperator::PROHIBITED()),
            new Mention('user_name', BoolOperator::PROHIBITED()),
            new Mention('user.name', BoolOperator::PROHIBITED()),
            new Mention('user-name', BoolOperator::PROHIBITED()),
        ],
    ],

    [
        'name'            => 'mentions with emails and hashtags',
        'input'           => '@john@doe.com @john#doe',
        'expected_tokens' => [
            [T::T_WORD, 'john@doe.com'],
            [T::T_WORD, 'john#doe'],
        ],
        'expected_nodes'  => [
            new Word('john@doe.com'),
            new Word('john#doe'),
        ],
    ],

    [
        'name'            => 'mentions with punctuation',
        'input'           => '@john. @wtf! @who?',
        'expected_tokens' => [
            [T::T_MENTION, 'john'],
            [T::T_MENTION, 'wtf'],
            [T::T_MENTION, 'who'],
        ],
        'expected_nodes'  => [
            new Mention('john', BoolOperator::REQUIRED()),
            new Mention('wtf', BoolOperator::REQUIRED()),
            new Mention('who', BoolOperator::REQUIRED()),
        ],
    ],

    [
        'name'            => 'mentions with special chars',
        'input'           => '@john^doe @john!doe',
        'expected_tokens' => [
            [T::T_MENTION, 'john'],
            T::T_BOOST,
            [T::T_WORD, 'doe'],
            [T::T_WORD, 'john!doe'],
        ],
        'expected_nodes'  => [
            new Mention('john', BoolOperator::REQUIRED()),
            new Word('doe'),
            new Word('john!doe'),
        ],
    ],
    /*
     * END: MENTIONS
     */


    /*
     * START: NUMBERS
     */
    [
        'name'            => 'integers, decimals and exponential form',
        'input'           => '100 3.1415926535898 2.2E-5',
        'expected_tokens' => [
            [T::T_NUMBER, 100.0],
            [T::T_NUMBER, 3.1415926535898],
            [T::T_NUMBER, 2.2E-5],
        ],
        'expected_nodes'  => [
            new Numbr(100.0),
            new Numbr(3.1415926535898),
            new Numbr(2.2E-5),
        ],
    ],

    [
        'name'            => 'negative integers, decimals and exponential form',
        'input'           => '-100 -3.1415926535898 -2.2E-5',
        'expected_tokens' => [
            [T::T_NUMBER, -100.0],
            [T::T_NUMBER, -3.1415926535898],
            [T::T_NUMBER, -2.2E-5],
        ],
        'expected_nodes'  => [
            new Numbr(-100.0),
            new Numbr(-3.1415926535898),
            new Numbr(-2.2E-5),
        ],
    ],

    [
        'name'            => 'words with boosted numbers',
        'input'           => 'word^100 word^3.1415926535898 word^2.2E-5',
        'expected_tokens' => [
            [T::T_WORD, 'word'],
            T::T_BOOST,
            [T::T_NUMBER, 100.0],
            [T::T_WORD, 'word'],
            T::T_BOOST,
            [T::T_NUMBER, 3.1415926535898],
            [T::T_WORD, 'word'],
            T::T_BOOST,
            [T::T_NUMBER, 2.2E-5],
        ],
        'expected_nodes'  => [
            new Word('word', null, true, 10.0),
            new Word('word', null, true, 3.1415926535898),
            new Word('word', null, true, 2.2E-5),
        ],
    ],

    [
        'name'            => 'words with boosted negative numbers',
        'input'           => 'word^-100 word^-3.1415926535898 word^-2.2E-5',
        'expected_tokens' => [
            [T::T_WORD, 'word'],
            T::T_BOOST,
            [T::T_NUMBER, -100.0],
            [T::T_WORD, 'word'],
            T::T_BOOST,
            [T::T_NUMBER, -3.1415926535898],
            [T::T_WORD, 'word'],
            T::T_BOOST,
            [T::T_NUMBER, -2.2E-5],
        ],
        'expected_nodes'  => [
            new Word('word', null, true, 0.0),
            new Word('word', null, true, 0.0),
            new Word('word', null, true, 0.0),
        ],
    ],

    [
        'name'            => 'words with fuzzy numbers',
        'input'           => 'word~100 word~3.1415926535898 word~2.2E-5',
        'expected_tokens' => [
            [T::T_WORD, 'word'],
            T::T_FUZZY,
            [T::T_NUMBER, 100.0],
            [T::T_WORD, 'word'],
            T::T_FUZZY,
            [T::T_NUMBER, 3.1415926535898],
            [T::T_WORD, 'word'],
            T::T_FUZZY,
            [T::T_NUMBER, 2.2E-5],
        ],
        'expected_nodes'  => [
            new Word('word', null, false, Word::DEFAULT_BOOST, true, Word::MAX_FUZZY),
            new Word('word', null, false, Word::DEFAULT_BOOST, true, Word::MAX_FUZZY),
            new Word('word', null, false, Word::DEFAULT_BOOST, true, Word::MIN_FUZZY),
        ],
    ],

    [
        'name'            => 'words with fuzzy negative numbers',
        'input'           => 'word~-100 word~-3.1415926535898 word~-2.2E-5',
        'expected_tokens' => [
            [T::T_WORD, 'word'],
            T::T_FUZZY,
            [T::T_NUMBER, -100.0],
            [T::T_WORD, 'word'],
            T::T_FUZZY,
            [T::T_NUMBER, -3.1415926535898],
            [T::T_WORD, 'word'],
            T::T_FUZZY,
            [T::T_NUMBER, -2.2E-5],
        ],
        'expected_nodes'  => [
            new Word('word', null, false, Word::DEFAULT_BOOST, true, Word::MIN_FUZZY),
            new Word('word', null, false, Word::DEFAULT_BOOST, true, Word::MIN_FUZZY),
            new Word('word', null, false, Word::DEFAULT_BOOST, true, Word::MIN_FUZZY),
        ],
    ],
    /*
     * END: NUMBERS
     */


    /*
     * START: FIELDS
     */
    [
        'name'            => 'fields with hypen, underscore and dot',
        'input'           => '+first-name:homer -last_name:simpson job.performance:poor^5',
        'expected_tokens' => [
            T::T_REQUIRED,
            [T::T_FIELD_START, 'first-name'],
            [T::T_WORD, 'homer'],
            T::T_FIELD_END,
            T::T_PROHIBITED,
            [T::T_FIELD_START, 'last_name'],
            [T::T_WORD, 'simpson'],
            T::T_FIELD_END,
            [T::T_FIELD_START, 'job.performance'],
            [T::T_WORD, 'poor'],
            T::T_FIELD_END,
            T::T_BOOST,
            [T::T_NUMBER, 5.0],
        ],
        'expected_nodes'  => [
            new Field('first-name', new Word('homer'), BoolOperator::REQUIRED(), false, Field::DEFAULT_BOOST),
            new Field('last_name', new Word('simpson'), BoolOperator::PROHIBITED(), false, Field::DEFAULT_BOOST),
            new Field('job.performance', new Word('poor'), null, true, 5.0),
        ],
    ],

    [
        'name'            => 'field with field in it',
        'input'           => 'field:subfield:what',
        'expected_tokens' => [
            [T::T_FIELD_START, 'field'],
            [T::T_WORD, 'subfield:what'],
            T::T_FIELD_END,
        ],
        'expected_nodes'  => [
            new Field('field', new Word('subfield:what'), null, false, Field::DEFAULT_BOOST),
        ],
    ],

    [
        'name'            => 'field with no value',
        'input'           => 'field:',
        'expected_tokens' => [
            [T::T_FIELD_START, 'field'],
            T::T_FIELD_END,
        ],
        'expected_nodes'  => [
            new Word('field'),
        ],
    ],

    [
        'name'            => 'field with phrases',
        'input'           => 'field:"boosted^5 +required"^1 -field:"[1..5]"~4',
        'expected_tokens' => [
            [T::T_FIELD_START, 'field'],
            [T::T_PHRASE, 'boosted^5 +required'],
            T::T_FIELD_END,
            T::T_BOOST,
            [T::T_NUMBER, 1.0],
            T::T_PROHIBITED,
            [T::T_FIELD_START, 'field'],
            [T::T_PHRASE, '[1..5]'],
            T::T_FIELD_END,
            T::T_FUZZY,
            [T::T_NUMBER, 4.0],
        ],
        'expected_nodes'  => [
            new Field('field', new Phrase('boosted^5 +required'), null, true, 1.0),
            new Field('field', new Phrase('[1..5]'), BoolOperator::PROHIBITED(), false, Field::DEFAULT_BOOST),
        ],
    ],

    [
        'name'            => 'field with greater/less than',
        'input'           => 'field:>100 field:>=100.1 field:<100 field:<=100.1',
        'expected_tokens' => [
            [T::T_FIELD_START, 'field'],
            T::T_GREATER_THAN,
            [T::T_NUMBER, 100.0],
            T::T_FIELD_END,
            [T::T_FIELD_START, 'field'],
            T::T_GREATER_THAN,
            T::T_EQUALS,
            [T::T_NUMBER, 100.1],
            T::T_FIELD_END,
            [T::T_FIELD_START, 'field'],
            T::T_LESS_THAN,
            [T::T_NUMBER, 100.0],
            T::T_FIELD_END,
            [T::T_FIELD_START, 'field'],
            T::T_LESS_THAN,
            T::T_EQUALS,
            [T::T_NUMBER, 100.1],
            T::T_FIELD_END,
        ],
        'expected_nodes'  => [
            new Field('field', new Numbr(100, ComparisonOperator::GT()), null, false, Field::DEFAULT_BOOST),
            new Field('field', new Numbr(100.1, ComparisonOperator::GTE()), null, false, Field::DEFAULT_BOOST),
            new Field('field', new Numbr(100, ComparisonOperator::LT()), null, false, Field::DEFAULT_BOOST),
            new Field('field', new Numbr(100.1, ComparisonOperator::LTE()), null, false, Field::DEFAULT_BOOST),
        ],
    ],

    [
        'name'            => 'field with a hashtag or mention',
        'input'           => 'field:#cats field:@user.name',
        'expected_tokens' => [
            [T::T_FIELD_START, 'field'],
            [T::T_HASHTAG, 'cats'],
            T::T_FIELD_END,
            [T::T_FIELD_START, 'field'],
            [T::T_MENTION, 'user.name'],
            T::T_FIELD_END,
        ],
        'expected_nodes'  => [
            new Field('field', new Hashtag('cats', BoolOperator::REQUIRED()), null, false, Field::DEFAULT_BOOST),
            new Field('field', new Mention('user.name', BoolOperator::REQUIRED()), null, false, Field::DEFAULT_BOOST),
        ],
    ],

    [
        'name'            => 'field with inclusive range',
        'input'           => 'field:[1..5] +field:[1 TO 5]',
        'expected_tokens' => [
            [T::T_FIELD_START, 'field'],
            T::T_RANGE_INCL_START,
            [T::T_NUMBER, 1.0],
            T::T_TO,
            [T::T_NUMBER, 5.0],
            T::T_RANGE_INCL_END,
            T::T_FIELD_END,
            T::T_REQUIRED,
            [T::T_FIELD_START, 'field'],
            T::T_RANGE_INCL_START,
            [T::T_NUMBER, 1.0],
            T::T_TO,
            [T::T_NUMBER, 5.0],
            T::T_RANGE_INCL_END,
            T::T_FIELD_END,
        ],
        'expected_nodes'  => [
            new Field(
                'field',
                new NumberRange(
                    new Numbr(1),
                    new Numbr(5)
                ),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
            new Field(
                'field',
                new NumberRange(
                    new Numbr(1),
                    new Numbr(5)
                ),
                BoolOperator::REQUIRED(),
                false,
                Field::DEFAULT_BOOST
            ),
        ],
    ],

    [
        'name'            => 'field with exclusive range',
        'input'           => 'field:{1.1..5.5} +field:{1.1 TO 5.5}',
        'expected_tokens' => [
            [T::T_FIELD_START, 'field'],
            T::T_RANGE_EXCL_START,
            [T::T_NUMBER, 1.1],
            T::T_TO,
            [T::T_NUMBER, 5.5],
            T::T_RANGE_EXCL_END,
            T::T_FIELD_END,
            T::T_REQUIRED,
            [T::T_FIELD_START, 'field'],
            T::T_RANGE_EXCL_START,
            [T::T_NUMBER, 1.1],
            T::T_TO,
            [T::T_NUMBER, 5.5],
            T::T_RANGE_EXCL_END,
            T::T_FIELD_END,
        ],
        'expected_nodes'  => [
            new Field(
                'field',
                new NumberRange(
                    new Numbr(1.1),
                    new Numbr(5.5),
                    true
                ),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
            new Field(
                'field',
                new NumberRange(
                    new Numbr(1.1),
                    new Numbr(5.5),
                    true
                ),
                BoolOperator::REQUIRED(),
                false,
                Field::DEFAULT_BOOST
            ),
        ],
    ],

    [
        'name'            => 'field with subquery',
        'input'           => 'field:(cat OR dog) test',
        'expected_tokens' => [
            [T::T_FIELD_START, 'field'],
            T::T_SUBQUERY_START,
            [T::T_WORD, 'cat'],
            T::T_OR,
            [T::T_WORD, 'dog'],
            T::T_SUBQUERY_END,
            T::T_FIELD_END,
            [T::T_WORD, 'test'],
        ],
        'expected_nodes'  => [
            new Field(
                'field',
                new Subquery([
                    new Word('cat'),
                    new Word('dog'),
                ]),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
            new Word('test'),
        ],
    ],

    [
        'name'            => 'field with range in subquery',
        'input'           => 'field:(cat OR 1..5)',
        'expected_tokens' => [
            [T::T_FIELD_START, 'field'],
            T::T_SUBQUERY_START,
            [T::T_WORD, 'cat'],
            T::T_OR,
            [T::T_NUMBER, 1.0],
            [T::T_NUMBER, 5.0],
            T::T_SUBQUERY_END,
            T::T_FIELD_END,
        ],
        'expected_nodes'  => [
            new Field(
                'field',
                new Subquery([
                    new Word('cat'),
                    new Numbr(1.0),
                    new Numbr(5.0),
                ]),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
        ],
    ],

    [
        'name'            => 'field with dates',
        'input'           => 'field:2015-12-18 field:>2015-12-18 field:<2015-12-18 field:>=2015-12-18 field:<=2015-12-18',
        'expected_tokens' => [
            [T::T_FIELD_START, 'field'],
            [T::T_DATE, '2015-12-18'],
            T::T_FIELD_END,
            [T::T_FIELD_START, 'field'],
            T::T_GREATER_THAN,
            [T::T_DATE, '2015-12-18'],
            T::T_FIELD_END,
            [T::T_FIELD_START, 'field'],
            T::T_LESS_THAN,
            [T::T_DATE, '2015-12-18'],
            T::T_FIELD_END,
            [T::T_FIELD_START, 'field'],
            T::T_GREATER_THAN,
            T::T_EQUALS,
            [T::T_DATE, '2015-12-18'],
            T::T_FIELD_END,
            [T::T_FIELD_START, 'field'],
            T::T_LESS_THAN,
            T::T_EQUALS,
            [T::T_DATE, '2015-12-18'],
            T::T_FIELD_END,
        ],
        'expected_nodes'  => [
            new Field(
                'field',
                new Date('2015-12-18'),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
            new Field(
                'field',
                new Date(
                    '2015-12-18',
                    null,
                    false,
                    Date::DEFAULT_BOOST,
                    false,
                    Date::DEFAULT_FUZZY,
                    ComparisonOperator::GT()
                ),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
            new Field(
                'field',
                new Date(
                    '2015-12-18',
                    null,
                    false,
                    Date::DEFAULT_BOOST,
                    false,
                    Date::DEFAULT_FUZZY,
                    ComparisonOperator::LT()
                ),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
            new Field(
                'field',
                new Date(
                    '2015-12-18',
                    null,
                    false,
                    Date::DEFAULT_BOOST,
                    false,
                    Date::DEFAULT_FUZZY,
                    ComparisonOperator::GTE()
                ),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
            new Field(
                'field',
                new Date(
                    '2015-12-18',
                    null,
                    false,
                    Date::DEFAULT_BOOST,
                    false,
                    Date::DEFAULT_FUZZY,
                    ComparisonOperator::LTE()
                ),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
        ],
    ],

    [
        'name'            => 'field leading _ and uuid',
        'input'           => '_id:a9fc3e46-150a-45cd-ad39-c80f93119900^5',
        'expected_tokens' => [
            [T::T_FIELD_START, '_id'],
            [T::T_WORD, 'a9fc3e46-150a-45cd-ad39-c80f93119900'],
            T::T_FIELD_END,
            T::T_BOOST,
            [T::T_NUMBER, 5.0],
        ],
        'expected_nodes'  => [
            new Field('_id', new Word('a9fc3e46-150a-45cd-ad39-c80f93119900'), null, true, 5.0),
        ],
    ],

    [
        'name'            => 'field with mentions and emails',
        'input'           => 'email:john@doe.com -user:@twitterz',
        'expected_tokens' => [
            [T::T_FIELD_START, 'email'],
            [T::T_WORD, 'john@doe.com'],
            T::T_FIELD_END,
            T::T_PROHIBITED,
            [T::T_FIELD_START, 'user'],
            [T::T_MENTION, 'twitterz'],
            T::T_FIELD_END,
        ],
        'expected_nodes'  => [
            new Field('email', new Word('john@doe.com'), null, false, Field::DEFAULT_BOOST),
            new Field(
                'user',
                new Mention('twitterz', BoolOperator::REQUIRED()),
                BoolOperator::PROHIBITED(),
                false,
                Field::DEFAULT_BOOST
            ),
        ],
    ],

    [
        'name'            => 'field with hashtags',
        'input'           => 'tags:#cats tags:(#cats || #dogs)',
        'expected_tokens' => [
            [T::T_FIELD_START, 'tags'],
            [T::T_HASHTAG, 'cats'],
            T::T_FIELD_END,
            [T::T_FIELD_START, 'tags'],
            T::T_SUBQUERY_START,
            [T::T_HASHTAG, 'cats'],
            T::T_OR,
            [T::T_HASHTAG, 'dogs'],
            T::T_SUBQUERY_END,
            T::T_FIELD_END,
        ],
        'expected_nodes'  => [
            new Field(
                'tags',
                new Hashtag('cats', BoolOperator::REQUIRED()),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
            new Field(
                'tags',
                new Subquery([
                    new Hashtag('cats', BoolOperator::REQUIRED()),
                    new Hashtag('dogs', BoolOperator::REQUIRED()),
                ]),
                null,
                false,
                Field::DEFAULT_BOOST
            ),
        ],
    ],
    /*
     * END: FIELDS
     */


    /*
     * START: WORDS
     */
    [
        'name'            => 'word with hashtag or mention in it',
        'input'           => 'omg#lol omg@user @mention#tag #tag@mention',
        'expected_tokens' => [
            [T::T_WORD, 'omg#lol'],
            [T::T_WORD, 'omg@user'],
            [T::T_WORD, 'mention#tag'],
            [T::T_WORD, 'tag@mention'],
        ],
        'expected_nodes'  => [
            new Word('omg#lol'),
            new Word('omg@user'),
            new Word('mention#tag'),
            new Word('tag@mention'),
        ],
    ],

    [
        'name'            => 'required/prohibited words',
        'input'           => '+c.h.u.d. -zombieland +ac/dc^5',
        'expected_tokens' => [
            T::T_REQUIRED,
            [T::T_WORD, 'c.h.u.d'],
            T::T_PROHIBITED,
            [T::T_WORD, 'zombieland'],
            T::T_REQUIRED,
            [T::T_WORD, 'ac/dc'],
            T::T_BOOST,
            [T::T_NUMBER, 5.0],
        ],
        'expected_nodes'  => [
            new Word('c.h.u.d', BoolOperator::REQUIRED()),
            new Word('zombieland', BoolOperator::PROHIBITED()),
            new Word('ac/dc', BoolOperator::REQUIRED(), true, 5.0),
        ],
    ],

    [
        'name'            => 'words that have embedded operators',
        'input'           => 'cANDy AND OReos || dANDy && chORes^5',
        'expected_tokens' => [
            [T::T_WORD, 'cANDy'],
            T::T_AND,
            [T::T_WORD, 'OReos'],
            T::T_OR,
            [T::T_WORD, 'dANDy'],
            T::T_AND,
            [T::T_WORD, 'chORes'],
            T::T_BOOST,
            [T::T_NUMBER, 5.0],
        ],
        'expected_nodes'  => [
            new Word('cANDy', BoolOperator::REQUIRED()),
            new Word('OReos', BoolOperator::REQUIRED()),
            new Word('dANDy', BoolOperator::REQUIRED()),
            new Word('chORes', BoolOperator::REQUIRED(), true, 5.0),
        ],
    ],
    /*
     * END: WORDS
     */


    /*
     * START: DATES
     */
    [
        'name'            => 'dates in string',
        'input'           => '2000-01-01 >=2000-01-01 (+2015-12-18) -2015-12-18',
        'expected_tokens' => [
            [T::T_DATE, '2000-01-01'],
            [T::T_DATE, '2000-01-01'],
            T::T_SUBQUERY_START,
            T::T_REQUIRED,
            [T::T_DATE, '2015-12-18'],
            T::T_SUBQUERY_END,
            T::T_PROHIBITED,
            [T::T_DATE, '2015-12-18'],
        ],
        'expected_nodes'  => [
            new Date('2000-01-01'),
            new Date('2000-01-01'),
            new Date('2015-12-18', BoolOperator::REQUIRED()),
            new Date('2015-12-18', BoolOperator::PROHIBITED()),
        ],
    ],

    [
        'name'            => 'dates on dates',
        'input'           => '2000-01-012000-01-01 2000-01-01^2000-01-01',
        'expected_tokens' => [
            [T::T_WORD, '2000-01-012000-01-01'],
            [T::T_DATE, '2000-01-01'],
            T::T_BOOST,
            [T::T_DATE, '2000-01-01'],
        ],
        'expected_nodes'  => [
            new Word('2000-01-012000-01-01'),
            new Date('2000-01-01'),
            new Date('2000-01-01'),
        ],
    ],
    /*
     * END: DATES
     */


    /*
     * START: ACCENTED CHARS
     */
    [
        'name'            => 'accents and hyphens',
        'input'           => '+Beyoncé Giselle Knowles-Carter',
        'expected_tokens' => [
            T::T_REQUIRED,
            [T::T_WORD, 'Beyoncé'],
            [T::T_WORD, 'Giselle'],
            [T::T_WORD, 'Knowles-Carter'],
        ],
        'expected_nodes'  => [
            new Word('Beyoncé', BoolOperator::REQUIRED()),
            new Word('Giselle'),
            new Word('Knowles-Carter'),
        ],
    ],

    [
        'name'            => 'accents and hyphen spice',
        'input'           => 'J. Lo => Emme Maribel Muñiz $p0rty-spicé',
        'expected_tokens' => [
            [T::T_WORD, 'J'],
            [T::T_WORD, 'Lo'],
            [T::T_WORD, 'Emme'],
            [T::T_WORD, 'Maribel'],
            [T::T_WORD, 'Muñiz'],
            [T::T_WORD, '$p0rty-spicé'],
        ],
        'expected_nodes'  => [
            new Word('J'),
            new Word('Lo'),
            new Word('Emme'),
            new Word('Maribel'),
            new Word('Muñiz'),
            new Word('$p0rty-spicé'),
        ],
    ],
    /*
     * END: ACCENTED CHARS
     */


    /*
     * START: RAPPERS and POP STARS
     */
    [
        'name'            => 'crazy a$$ names',
        'input'           => 'p!nk AND K$sha in a tr33 with 50¢',
        'expected_tokens' => [
            [T::T_WORD, 'p!nk'],
            T::T_AND,
            [T::T_WORD, 'K$sha'],
            [T::T_WORD, 'in'],
            [T::T_WORD, 'a'],
            [T::T_WORD, 'tr33'],
            [T::T_WORD, 'with'],
            [T::T_WORD, '50¢'],
        ],
        'expected_nodes'  => [
            new Word('p!nk', BoolOperator::REQUIRED()),
            new Word('K$sha', BoolOperator::REQUIRED()),
            new Word('in'),
            new Word('a'),
            new Word('tr33'),
            new Word('with'),
            new Word('50¢'),
        ],
    ],

    [
        'name'            => 'my name is math(ish)',
        'input'           => '+florence+machine ac/dc^11 Stellastarr* T\'Pau ​¡Forward, Russia! "¡Forward, Russia!"~',
        'expected_tokens' => [
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
            [T::T_PHRASE, '¡Forward, Russia!'],
            T::T_FUZZY,
        ],
        'expected_nodes'  => [
            new Word('florence+machine', BoolOperator::REQUIRED()),
            new Word('ac/dc', null, true, Word::MAX_BOOST),
            new Word('Stellastarr', null, false, Word::DEFAULT_BOOST, false, Word::DEFAULT_FUZZY, true),
            new Word('T\'Pau'),
            new Word('​¡Forward'),
            new Word('Russia'),
            new Phrase('¡Forward, Russia!', null, false, Phrase::DEFAULT_BOOST, true, Phrase::DEFAULT_FUZZY),
        ],
    ],
    /*
     * END: RAPPERS and POP STARS
     */


    /*
     * START: SUBQUERIES
     */
    [
        'name'            => 'mismatched subqueries',
        'input'           => ') test (123 (abc f:a)',
        'expected_tokens' => [
            [T::T_WORD, 'test'],
            T::T_SUBQUERY_START,
            [T::T_NUMBER, 123.0],
            [T::T_WORD, 'abc'],
            [T::T_WORD, 'f:a'],
            T::T_SUBQUERY_END,
        ],
        'expected_nodes'  => [
            new Word('test'),
            new Subquery([new Numbr(123.0), new Word('abc'), new Word('f:a')]),
        ],
    ],

    [
        'name'            => 'filter inside of subquery',
        'input'           => 'word(word:a>(#hashtag:b)',
        'expected_tokens' => [
            [T::T_WORD, 'word'],
            T::T_SUBQUERY_START,
            [T::T_WORD, 'word:a'],
            [T::T_WORD, 'hashtag:b'],
            T::T_SUBQUERY_END,
        ],
        'expected_nodes'  => [
            new Word('word'),
            new Subquery([new Word('word:a'), new Word('hashtag:b')]),
        ],
    ],

    [
        'name'            => 'booleans before and in subqueries',
        'input'           => '"ipad pro" AND (gold OR silver)',
        'expected_tokens' => [
            [T::T_PHRASE, 'ipad pro'],
            T::T_AND,
            T::T_SUBQUERY_START,
            [T::T_WORD, 'gold'],
            T::T_OR,
            [T::T_WORD, 'silver'],
            T::T_SUBQUERY_END,
        ],
        'expected_nodes'  => [
            new Phrase('ipad pro', BoolOperator::REQUIRED()),
            new Subquery([new Word('gold'), new Word('silver')], BoolOperator::REQUIRED()),
        ],
    ],

    [
        'name'            => 'booleans before and in subqueries 2',
        'input'           => '"iphone 7" -(16gb OR 32gb)',
        'expected_tokens' => [
            [T::T_PHRASE, 'iphone 7'],
            T::T_PROHIBITED,
            T::T_SUBQUERY_START,
            [T::T_WORD, '16gb'],
            T::T_OR,
            [T::T_WORD, '32gb'],
            T::T_SUBQUERY_END,
        ],
        'expected_nodes'  => [
            new Phrase('iphone 7'),
            new Subquery([new Word('16gb'), new Word('32gb')], BoolOperator::PROHIBITED()),
        ],
    ],
    /*
     * END: SUBQUERIES
     */


    /*
     * START: WEIRD QUERIES
     */
    [
        'name'            => 'whip nae nae',
        'input'           => 'Watch Me (Whip/Nae Nae)',
        'expected_tokens' => [
            [T::T_WORD, 'Watch'],
            [T::T_WORD, 'Me'],
            T::T_SUBQUERY_START,
            [T::T_WORD, 'Whip/Nae'],
            [T::T_WORD, 'Nae'],
            T::T_SUBQUERY_END,
        ],
        'expected_nodes'  => [
            new Word('Watch'),
            new Word('Me'),
            new Subquery([new Word('Whip/Nae'), new Word('Nae')]),
        ],
    ],

    [
        'name'            => 'epic or fail',
        'input'           => 'epic or fail',
        'expected_tokens' => [
            [T::T_WORD, 'epic'],
            [T::T_WORD, 'or'],
            [T::T_WORD, 'fail'],
        ],
        'expected_nodes'  => [
            new Word('epic'),
            new Word('or'),
            new Word('fail'),
        ],
    ],

    [
        'name'            => 'use of || then and required subquery',
        'input'           => 'test || AND what (+test)',
        'expected_tokens' => [
            [T::T_WORD, 'test'],
            T::T_OR,
            T::T_AND,
            [T::T_WORD, 'what'],
            T::T_SUBQUERY_START,
            T::T_REQUIRED,
            [T::T_WORD, 'test'],
            T::T_SUBQUERY_END,
        ],
        'expected_nodes'  => [
            new Word('test'),
            new Word('what', BoolOperator::REQUIRED()),
            new Word('test', BoolOperator::REQUIRED()),
        ],
    ],

    [
        'name'            => 'mega subqueries, all non-sensical',
        'input'           => 'test OR ( ( 1 ) OR ( ( 2 ) ) OR ( ( ( 3.14 ) ) ) OR a OR +b ) OR +field:>1',
        'expected_tokens' => [
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
            [T::T_FIELD_START, 'field'],
            T::T_GREATER_THAN,
            [T::T_NUMBER, 1.0],
            T::T_FIELD_END,
        ],
        'expected_nodes'  => [
            new Word('test'),
            new Numbr(1),
            new Numbr(2),
            new Numbr(3.14),
            new Word('a'),
            new Word('b', BoolOperator::REQUIRED()),
            new Field(
                'field',
                new Numbr(1.0, ComparisonOperator::GT()),
                BoolOperator::REQUIRED(),
                false,
                Field::DEFAULT_BOOST
            ),
        ],
    ],

    [
        'name'            => 'common dotted things',
        'input'           => 'R.I.P. Motörhead',
        'expected_tokens' => [
            [T::T_WORD, 'R.I.P'],
            [T::T_WORD, 'Motörhead'],
        ],
        'expected_nodes'  => [
            new Word('R.I.P'),
            new Word('Motörhead'),
        ],
    ],

    [
        'name'            => 'ignored chars',
        'input'           => '!!! ! $ _ . ; %',
        'expected_tokens' => [],
        'expected_nodes'  => [],
    ],

    [
        'name'            => 'elastic search example 1',
        'input'           => '"john smith"^2   (foo bar)^4',
        'expected_tokens' => [
            [T::T_PHRASE, 'john smith'],
            T::T_BOOST,
            [T::T_NUMBER, 2.0],
            T::T_SUBQUERY_START,
            [T::T_WORD, 'foo'],
            [T::T_WORD, 'bar'],
            T::T_SUBQUERY_END,
            T::T_BOOST,
            [T::T_NUMBER, 4.0],
        ],
        'expected_nodes'  => [
            new Phrase('john smith', null, true, 2.0),
            new Subquery([new Word('foo'), new Word('bar')], null, true, 4.0),
        ],
    ],

    [
        'name'            => 'intentionally mutant',
        'input'           => '[blah "[[shortcode]]" akd_ -gj% ! @* (+=} --> ;\' <a onclick="javascript:alert(\'test\')>click</a>',
        'expected_tokens' => [
            [T::T_WORD, 'blah'],
            [T::T_PHRASE, '[[shortcode]]'],
            [T::T_WORD, 'akd_'],
            T::T_PROHIBITED,
            [T::T_WORD, 'gj%'],
            T::T_SUBQUERY_START,
            T::T_PROHIBITED,
            [T::T_WORD, 'a'],
            [T::T_WORD, 'onclick'],
            [T::T_WORD, 'javascript:alert'],
            [T::T_WORD, 'test'],
            T::T_SUBQUERY_END,
            [T::T_WORD, 'click'],
            [T::T_WORD, 'a'],
        ],
        'expected_nodes'  => [
            new Word('blah'),
            new Phrase('[[shortcode]]'),
            new Word('akd_'),
            new Word('gj%', BoolOperator::PROHIBITED()),
            new Subquery([
                new Word('a', BoolOperator::PROHIBITED()),
                new Word('onclick'),
                new Word('javascript:alert'),
                new Word('test'),
            ]),
            new Word('click'),
            new Word('a'),
        ],
    ],

    [
        'name'            => 'intentionally mutanter',
        'input'           => '[blah &quot;[[shortcode]]&quot; akd_ -gj% ! @* (+=} --&gt; ;\' &lt;a onclick=&quot;javascript:alert(\'test\')&gt;click&lt;/a&gt;',
        'expected_tokens' => [
            [T::T_WORD, 'blah'],
            [T::T_WORD, 'quot'],
            [T::T_WORD, 'shortcode'],
            [T::T_WORD, 'quot'],
            [T::T_WORD, 'akd_'],
            T::T_PROHIBITED,
            [T::T_WORD, 'gj%'],
            T::T_SUBQUERY_START,
            T::T_PROHIBITED,
            [T::T_WORD, 'gt'],
            [T::T_WORD, 'lt;a'],
            [T::T_WORD, 'onclick'],
            [T::T_WORD, 'quot;javascript:alert'],
            [T::T_WORD, 'test'],
            T::T_SUBQUERY_END,
            [T::T_WORD, 'gt;click&lt;/a&gt'],
        ],
        'expected_nodes'  => [
            new Word('blah'),
            new Word('quot'),
            new Word('shortcode'),
            new Word('quot'),
            new Word('akd_'),
            new Word('gj%', BoolOperator::PROHIBITED()),
            new Subquery([
                new Word('gt', BoolOperator::PROHIBITED()),
                new Word('lt;a'),
                new Word('onclick'),
                new Word('quot;javascript:alert'),
                new Word('test'),
            ]),
            new Word('gt;click&lt;/a&gt'),
        ],
    ],

    [
        'name'            => 'intentionally mutanterer',
        'input'           => 'a"b"#c"#d e',
        'expected_tokens' => [
            [T::T_WORD, 'a"b"#c"#d'],
            [T::T_WORD, 'e'],
        ],
        'expected_nodes'  => [
            new Word('a"b"#c"#d'),
            new Word('e'),
        ],
    ],

    [
        'name'            => 'xss1',
        'input'           => '<IMG SRC=j&#X41vascript:alert(\'test2\')>',
        'expected_tokens' => [
            [T::T_WORD, 'IMG'],
            [T::T_WORD, 'SRC'],
            [T::T_WORD, 'j&#X41vascript:alert'],
            T::T_SUBQUERY_START,
            [T::T_WORD, 'test2'],
            T::T_SUBQUERY_END,
        ],
        'expected_nodes'  => [
            new Word('IMG'),
            new Word('SRC'),
            new Word('j&#X41vascript:alert'),
            new Word('test2'),
        ],
    ],

    [
        'name'            => 'should not be required',
        'input'           => 'token + token',
        'expected_tokens' => [
            [T::T_WORD, 'token'],
            [T::T_WORD, 'token'],
        ],
        'expected_nodes'  => [
            new Word('token'),
            new Word('token'),
        ],
    ],

    [
        'name'            => 'should not be prohibited',
        'input'           => 'token - token',
        'expected_tokens' => [
            [T::T_WORD, 'token'],
            [T::T_WORD, 'token'],
        ],
        'expected_nodes'  => [
            new Word('token'),
            new Word('token'),
        ],
    ],

    [
        'name'            => 'should not be boosted',
        'input'           => 'token ^5 token',
        'expected_tokens' => [
            [T::T_WORD, 'token'],
            [T::T_NUMBER, 5.0],
            [T::T_WORD, 'token'],
        ],
        'expected_nodes'  => [
            new Word('token'),
            new Numbr(5.0),
            new Word('token'),
        ],
    ],

    [
        'name'            => 'should not have words or phrases without real characters',
        'input'           => 'test taco-spice chester:copperpot :: : ; ;; " " , - -- - ++ "a phrase:" _ [ ] { } | \\ / ` * ~ ! @ ( ) # $ % ^ & = < > ?',
        'expected_tokens' => [
            [T::T_WORD, 'test'],
            [T::T_WORD, 'taco-spice'],
            [T::T_FIELD_START, 'chester'],
            [T::T_WORD, 'copperpot'],
            T::T_FIELD_END,
            T::T_PROHIBITED,
            T::T_REQUIRED,
            [T::T_PHRASE, 'a phrase:'],
            T::T_WILDCARD,
            T::T_SUBQUERY_START,
            T::T_SUBQUERY_END,
        ],
        'expected_nodes'  => [
            new Word('test'),
            new Word('taco-spice'),
            new Field('chester', new Word('copperpot')),
            new Phrase('a phrase:'),
        ],
    ],
    /*
     * END: WEIRD QUERIES
     */
];
