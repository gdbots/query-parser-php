<?php

use Gdbots\QueryParser\Parser\QueryParser;
use Gdbots\QueryParser\Visitor\QueryItemPrinter;

$parser = new QueryParser();
$parser->readString('a+b -c +d (f:v^1.5+6)');

if ($query = $parser->parse()) {
    echo "Print output:\n\n";
    $query->accept(new QueryItemPrinter());

/*
 Or
> Word: a
> Word: b
> ExcludeTerm
>> Word: c
> IncludeTerm
>> Word: d
> Subexpression
>> Or
>>> Term: ^ 1.5
>>>> Term: f : v
>>> Word: 6
*/

    echo "\n\nCompiled query:\n\n";
    echo $parser->getScanner()->getProcessedData();

/*
a OR b OR -c OR +d OR ( f:v^1.5 OR 6 )
*/

    echo "\n\nQuery object:\n\n";
    var_dump($query);

/*
object(Gdbots\QueryParser\Node\OrExpressionList)#596 (1) {
  ["expressions":protected]=>
  array(5) {
    [0]=>
    object(Gdbots\QueryParser\Node\Word)#582 (2) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "a"
    }
    [1]=>
    object(Gdbots\QueryParser\Node\Word)#584 (2) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "b"
    }
    [2]=>
    object(Gdbots\QueryParser\Node\ExcludeTerm)#586 (1) {
      ["expression":protected]=>
      object(Gdbots\QueryParser\Node\Word)#585 (2) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "c"
      }
    }
    [3]=>
    object(Gdbots\QueryParser\Node\IncludeTerm)#589 (1) {
      ["expression":protected]=>
      object(Gdbots\QueryParser\Node\Word)#587 (2) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "d"
      }
    }
    [4]=>
    object(Gdbots\QueryParser\Node\SubExpression)#597 (1) {
      ["expression":protected]=>
      object(Gdbots\QueryParser\Node\OrExpressionList)#595 (1) {
        ["expressions":protected]=>
        array(2) {
          [0]=>
          object(Gdbots\QueryParser\Node\ExplicitTerm)#593 (4) {
            ["nominator":protected]=>
            object(Gdbots\QueryParser\Node\ExplicitTerm)#592 (4) {
              ["nominator":protected]=>
              object(Gdbots\QueryParser\Node\Word)#588 (2) {
                ["tokenType":protected]=>
                int(1)
                ["token":protected]=>
                string(1) "f"
              }
              ["tokenType":protected]=>
              int(8)
              ["tokenTypeText":protected]=>
              string(1) ":"
              ["term":protected]=>
              object(Gdbots\QueryParser\Node\Word)#590 (2) {
                ["tokenType":protected]=>
                int(1)
                ["token":protected]=>
                string(1) "v"
              }
            }
            ["tokenType":protected]=>
            int(9)
            ["tokenTypeText":protected]=>
            string(1) "^"
            ["term":protected]=>
            object(Gdbots\QueryParser\Node\Word)#591 (2) {
              ["tokenType":protected]=>
              int(1)
              ["token":protected]=>
              string(3) "1.5"
            }
          }
          [1]=>
          object(Gdbots\QueryParser\Node\Word)#594 (2) {
            ["tokenType":protected]=>
            int(1)
            ["token":protected]=>
            string(1) "6"
          }
        }
      }
    }
  }
}
*/

    echo "\n\nGroup objects:\n\n";
    var_dump($query->getQueryItemsByTokenType());

/*
array(5) {
  ["WORD"]=>
  array(3) {
    [0]=>
    object(Gdbots\QueryParser\Node\Word)#582 (2) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "a"
    }
    [1]=>
    object(Gdbots\QueryParser\Node\Word)#584 (2) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "b"
    }
    [2]=>
    object(Gdbots\QueryParser\Node\Word)#594 (2) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "6"
    }
  }
  ["EXCLUDE"]=>
  array(1) {
    [0]=>
    object(Gdbots\QueryParser\Node\ExcludeTerm)#586 (1) {
      ["expression":protected]=>
      object(Gdbots\QueryParser\Node\Word)#585 (2) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "c"
      }
    }
  }
  ["INCLUDE"]=>
  array(1) {
    [0]=>
    object(Gdbots\QueryParser\Node\IncludeTerm)#589 (1) {
      ["expression":protected]=>
      object(Gdbots\QueryParser\Node\Word)#587 (2) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "d"
      }
    }
  }
  ["BOOST"]=>
  array(1) {
    [0]=>
    object(Gdbots\QueryParser\Node\ExplicitTerm)#593 (4) {
      ["nominator":protected]=>
      object(Gdbots\QueryParser\Node\ExplicitTerm)#592 (4) {
        ["nominator":protected]=>
        object(Gdbots\QueryParser\Node\Word)#588 (2) {
          ["tokenType":protected]=>
          int(1)
          ["token":protected]=>
          string(1) "f"
        }
        ["tokenType":protected]=>
        int(8)
        ["tokenTypeText":protected]=>
        string(1) ":"
        ["term":protected]=>
        object(Gdbots\QueryParser\Node\Word)#590 (2) {
          ["tokenType":protected]=>
          int(1)
          ["token":protected]=>
          string(1) "v"
        }
      }
      ["tokenType":protected]=>
      int(9)
      ["tokenTypeText":protected]=>
      string(1) "^"
      ["term":protected]=>
      object(Gdbots\QueryParser\Node\Word)#591 (2) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(3) "1.5"
      }
    }
  }
  ["FILTER"]=>
  array(1) {
    [0]=>
    object(Gdbots\QueryParser\Node\ExplicitTerm)#592 (4) {
      ["nominator":protected]=>
      object(Gdbots\QueryParser\Node\Word)#588 (2) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "f"
      }
      ["tokenType":protected]=>
      int(8)
      ["tokenTypeText":protected]=>
      string(1) ":"
      ["term":protected]=>
      object(Gdbots\QueryParser\Node\Word)#590 (2) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "v"
      }
    }
  }
}
*/

}
if ($parser->hasErrors()) {
    var_dump($parser->getErrors());
}
