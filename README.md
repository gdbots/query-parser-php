query-parser-php
================

Php library that converts search queries into terms, phrases, hashtags, mentions, etc.

## Basic Usage

``` php
<?php

use Gdbots\QueryParser\SearchQueryParser;

$query = new SearchQueryParser();

$query->setQuery('+mandatoryWord -excludedWord fieldName:"value"');
var_dump($query->evaluate());

$query->setQuery('a AND (b OR c) AND NOT d');
var_dump($query->evaluate());

```
