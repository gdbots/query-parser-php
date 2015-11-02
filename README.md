QueryParser Handler
===================

Php library that converts search queries into terms, phrases, hashtags, mentions, etc.

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
use Gdbots\QueryParser\Visitor\QueryItemPrinter;

$parser = new QueryParser();
$query = $parser->parse('+mandatoryWord AND -excludedWord fieldName:"value"');

$printer = new QueryItemPrinter();
$query->accept($printer);
```

**Result:**

```
 And
> Word (WORD): +mandatoryWord
> Or
>> Word (WORD): -excludedWord
>> Term: fieldName : value
```

> **Note:** You can also ignore operator by using `$parser->parse('search query here', true)`, which will remove all AND and brackets.
For the above example the result would be:

```
 Or
> Word (WORD): +mandatoryWord
> Word (WORD): -excludedWord
> Term: fieldName : value
```

To pull list of `QueryItem` by token type, use:

``` php
<?php

use Gdbots\QueryParser\QueryParser;
use Gdbots\QueryParser\QueryLexer;

$parser = new QueryParser();
$query = $parser->parse('#hashtag1 AND #hashtag2');

$hashtags = $query->getQueryItemsByTokenType(QueryLexer::T_HASHTAG);
var_dump($hashtags);
```

**OR:**

``` php
<?php

use Gdbots\QueryParser\QueryResult;

$result = new QueryResult();
$allTokens = $result->parse('#hashtag1 AND #hashtag2');

$hashtags = $result->getHashtags();
var_dump($hashtags);
```

> **Note:** You can also enable operator by using pasing **false** to `$result->parse()`.

**Result:**

```
array(2) {
  [0]=>
  object(Gdbots\QueryParser\Node\Hashtag)#589 (5) {
    ["tokenType":protected]=>
    int(12)
    ["token":protected]=>
    string(8) "hashtag1"
    ["excluded":protected]=>
    bool(false)
    ["included":protected]=>
    bool(false)
    ["boostBy":protected]=>
    NULL
  }
  [1]=>
  object(Gdbots\QueryParser\Node\Hashtag)#588 (5) {
    ["tokenType":protected]=>
    int(12)
    ["token":protected]=>
    string(8) "hashtag2"
    ["excluded":protected]=>
    bool(false)
    ["included":protected]=>
    bool(false)
    ["boostBy":protected]=>
    NULL
  }
}
```
