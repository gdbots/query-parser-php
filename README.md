query-parser-php
================

Php library that converts search queries into terms, phrases, hashtags, mentions, etc.

## Basic Usage

``` php
<?php

use Gdbots\QueryParser\Parser\QueryParser;

$parser = new QueryParser();
$parser->readString('mandatoryWord AND -excludedWord fieldName:"value"');
$query = $parser->parse();
var_dump($query);

$parser->readString('a AND (b OR c) AND NOT d');
$query = $parser->parse();
var_dump($query);

```
