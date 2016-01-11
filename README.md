query-parser-php
=============

[![Build Status](https://api.travis-ci.org/gdbots/query-parser-php.svg)](https://travis-ci.org/gdbots/query-parser-php)
[![Code Climate](https://codeclimate.com/github/gdbots/query-parser-php/badges/gpa.svg)](https://codeclimate.com/github/gdbots/query-parser-php)
[![Test Coverage](https://codeclimate.com/github/gdbots/query-parser-php/badges/coverage.svg)](https://codeclimate.com/github/gdbots/query-parser-php/coverage)

Php library that converts search queries into words, phrases, hashtags, mentions, etc.

This library supports a simple search query standard. It is meant to support the most common search combinations that a
user would likely enter into your website search box or dashboard application.  It intentionally limits the more complex nested capabilities
that you might expect from SQL builders, Lucene, etc.


## Tokenizer
Tokens are split on whitespace unless enclosed in double quotes.  The following tokens are extracted by the `Tokenizer`:

``` php
class Token implements \JsonSerializable
{
    const T_EOI              = 0;  // end of input
    const T_WHITE_SPACE      = 1;
    const T_IGNORED          = 2;  // an ignored token, e.g. #, !, etc.  when found by themselves, don't do anything with them.
    const T_NUMBER           = 3;  // 10, 0.8, .64, 6.022e23
    const T_REQUIRED         = 4;  // '+'
    const T_PROHIBITED       = 5;  // '-'
    const T_GREATER_THAN     = 6;  // '>'
    const T_LESS_THAN        = 7;  // '<'
    const T_EQUALS           = 8;  // '='
    const T_FUZZY            = 9;  // '~'
    const T_BOOST            = 10; // '^'
    const T_RANGE_INCL_START = 11; // '['
    const T_RANGE_INCL_END   = 12; // ']'
    const T_RANGE_EXCL_START = 13; // '{'
    const T_RANGE_EXCL_END   = 14; // '}'
    const T_SUBQUERY_START   = 15; // '('
    const T_SUBQUERY_END     = 16; // ')'
    const T_WILDCARD         = 17; // '*'
    const T_AND              = 18; // 'AND' or '&&'
    const T_OR               = 19; // 'OR' or '||'
    const T_TO               = 20; // 'TO' or '..'
    const T_WORD             = 21;
    const T_FIELD_START      = 22; // The "field:" portion of "field:value".
    const T_FIELD_END        = 23; // when a field lexeme ends, i.e. "field:value". This token has no value.
    const T_PHRASE           = 24; // Phrase (one or more quoted words)
    const T_URL              = 25; // a valid url
    const T_DATE             = 26; // date in the format YYYY-MM-DD
    const T_HASHTAG          = 27; // #hashtag
    const T_MENTION          = 28; // @mention
    const T_EMOTICON         = 29; // see https://en.wikipedia.org/wiki/Emoticon
    const T_EMOJI            = 30; // see https://en.wikipedia.org/wiki/Emoji
```
The `T_WHITE_SPACE` and `T_IGNORED` tokens are removed before the output is returned by the scan process.


## QueryParser

The default query parser produces a `ParsedQuery` object which can be used with a builder to produce a query
for a given search service.


#### Basic Usage

``` php
<?php

use Gdbots\QueryParser\QueryParser;
use Gdbots\QueryParser\Builder\XmlQueryBuilder;

$parser  = new QueryParser();
$builder = (new XmlQueryBuilder())->setHashtagFieldName('tags');

$result = $parser->parse('hello^5 planet:earth +date:2015-12-25 #omg');
echo $builder->addParsedQuery($result)->toXmlString();
```
Produces the following xml:
``` xml
<?xml version="1.0"?>
<query>
  <word boost="5" rule="should_match">hello</word>
  <field name="planet">
    <word rule="should_match_term">earth</word>
  </field>
  <field name="date" bool_operator="required" cacheable="true">
    <date rule="must_not_match_term">2015-12-25</date>
  </field>
  <field name="tags" bool_operator="required" cacheable="true">
    <hashtag rule="must_match_term">omg</hashtag>
  </field>
</query>
```

To pull list of `Node` objects by type, use:

``` php
<?php

$result = $parser->parse('#hashtag1 AND #hashtag2');
$hashtags = $result->getNodesOfType(\Gdbots\QueryParser\Node\Hashtag::NODE_TYPE);
```
