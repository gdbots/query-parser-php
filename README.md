query-parser-php
=============

[![Build Status](https://api.travis-ci.org/gdbots/query-parser-php.svg)](https://travis-ci.org/gdbots/query-parser-php)
[![Code Climate](https://codeclimate.com/github/gdbots/query-parser-php/badges/gpa.svg)](https://codeclimate.com/github/gdbots/query-parser-php)
[![Test Coverage](https://codeclimate.com/github/gdbots/query-parser-php/badges/coverage.svg)](https://codeclimate.com/github/gdbots/query-parser-php/coverage)

Php library that converts search queries into words, phrases, hashtags, mentions, etc.


## Query Syntax

The query parser supports a fairly universal search query standard. It is
meant to support the most common search combinations while be fairly simple to
use by non-technical users.

It supports the following features:
* Keywords are split on whitespace, ex. `word1 word2` contains two keywords.
* Keywords can be grouped using quotation marks, ex. `"word1 word2"` only contains one keyword.
* Keywords can be combined using boolean and, ex. `word1 AND word2`. This is also the default combination, so `word1 word2` is the same as `word1 AND word2`.
* Keywords can be combined using boolean or, ex. `word1 OR word2`.
* Keywords can be exclude or include using "-" or "+", ex. `-excludedWord +mandatoryWord`.
* Combinational logic can be specified using parentheses, ex. `word1 OR (word2 AND word3)`.
* A keyword can be explicitly marked as belonging to a certain domain, ex. `people:Michael`.


## Basic Usage

``` php
<?php

use Gdbots\QueryParser\QueryParser;
use Gdbots\QueryParser\Builder\PrettyPrinter;

$parser  = new QueryParser();
$printer = new PrettyPrinter();

/** @var \Gdbots\QueryParser\ParsedQuery */
$result = $parser->parse('+mandatoryWord AND -excludedWord fieldName:"value"');

echo $printer->fromParsedQuery($result)->getResult();
```

To pull list of `Node` by type, use:

``` php
<?php

$result = $parser->parse('#hashtag1 AND #hashtag2');

$hashtags = $result->getNodesOfType(\Gdbots\QueryParser\Node\Hashtag::NODE_TYPE);
```
