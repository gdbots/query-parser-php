<?php

require dirname(__DIR__) . '/../vendor/autoload.php';

use Gdbots\QueryParser\QueryParser;
use Gdbots\QueryParser\Visitor\QueryItemPrinter;

$parser = new QueryParser();
$parser->readString('a+b -c +d (f:v^1.5+6)');

if ($query = $parser->parse()) {
    echo "Print output:\n\n";
    $query->accept(new QueryItemPrinter());

/*
 Or
> Word: a
> Word: -b
> Word: +c
> Word: -d
> Subexpression
>> Or
>>> Term: f : v ^ 1.50
>>> Word: -6
*/

    echo "\n\nCompiled query:\n\n";
    echo $parser->getLexer()->getProcessedData();

/*
a OR +b OR -c OR +d OR ( f:v^1.5 OR +6 )
*/

    echo "\n\nQuery object:\n\n";
    var_dump($query);

/*
object(Gdbots\QueryParser\Node\OrExpressionList)#597 (4) {
  ["expressions":protected]=>
  array(5) {
    [0]=>
    object(Gdbots\QueryParser\Node\Word)#586 (5) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "a"
      ["excluded":protected]=>
      bool(false)
      ["included":protected]=>
      bool(false)
      ["boostBy":protected]=>
      NULL
    }
    [1]=>
    object(Gdbots\QueryParser\Node\Word)#588 (5) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "b"
      ["excluded":protected]=>
      bool(false)
      ["included":protected]=>
      bool(true)
      ["boostBy":protected]=>
      NULL
    }
    [2]=>
    object(Gdbots\QueryParser\Node\Word)#589 (5) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "c"
      ["excluded":protected]=>
      bool(true)
      ["included":protected]=>
      bool(false)
      ["boostBy":protected]=>
      NULL
    }
    [3]=>
    object(Gdbots\QueryParser\Node\Word)#590 (5) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "d"
      ["excluded":protected]=>
      bool(false)
      ["included":protected]=>
      bool(true)
      ["boostBy":protected]=>
      NULL
    }
    [4]=>
    object(Gdbots\QueryParser\Node\SubExpression)#593 (4) {
      ["expression":protected]=>
      object(Gdbots\QueryParser\Node\OrExpressionList)#596 (4) {
        ["expressions":protected]=>
        array(2) {
          [0]=>
          object(Gdbots\QueryParser\Node\ExplicitTerm)#594 (7) {
            ["nominator":protected]=>
            object(Gdbots\QueryParser\Node\Word)#591 (5) {
              ["tokenType":protected]=>
              int(1)
              ["token":protected]=>
              string(1) "f"
              ["excluded":protected]=>
              bool(false)
              ["included":protected]=>
              bool(false)
              ["boostBy":protected]=>
              NULL
            }
            ["tokenType":protected]=>
            int(9)
            ["tokenTypeText":protected]=>
            string(1) ":"
            ["term":protected]=>
            object(Gdbots\QueryParser\Node\Word)#592 (5) {
              ["tokenType":protected]=>
              int(1)
              ["token":protected]=>
              string(1) "v"
              ["excluded":protected]=>
              bool(false)
              ["included":protected]=>
              bool(false)
              ["boostBy":protected]=>
              NULL
            }
            ["excluded":protected]=>
            bool(false)
            ["included":protected]=>
            bool(false)
            ["boostBy":protected]=>
            string(3) "1.5"
          }
          [1]=>
          object(Gdbots\QueryParser\Node\Word)#595 (5) {
            ["tokenType":protected]=>
            int(1)
            ["token":protected]=>
            string(1) "6"
            ["excluded":protected]=>
            bool(false)
            ["included":protected]=>
            bool(true)
            ["boostBy":protected]=>
            NULL
          }
        }
        ["excluded":protected]=>
        bool(false)
        ["included":protected]=>
        bool(false)
        ["boostBy":protected]=>
        NULL
      }
      ["excluded":protected]=>
      bool(false)
      ["included":protected]=>
      bool(false)
      ["boostBy":protected]=>
      NULL
    }
  }
  ["excluded":protected]=>
  bool(false)
  ["included":protected]=>
  bool(false)
  ["boostBy":protected]=>
  NULL
}
*/

    echo "\n\nGroup objects:\n\n";
    var_dump($query->getQueryItemsByTokenType());

/*
array(2) {
  ["WORD"]=>
  array(5) {
    [0]=>
    object(Gdbots\QueryParser\Node\Word)#586 (5) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "a"
      ["excluded":protected]=>
      bool(false)
      ["included":protected]=>
      bool(false)
      ["boostBy":protected]=>
      NULL
    }
    [1]=>
    object(Gdbots\QueryParser\Node\Word)#588 (5) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "b"
      ["excluded":protected]=>
      bool(false)
      ["included":protected]=>
      bool(true)
      ["boostBy":protected]=>
      NULL
    }
    [2]=>
    object(Gdbots\QueryParser\Node\Word)#589 (5) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "c"
      ["excluded":protected]=>
      bool(true)
      ["included":protected]=>
      bool(false)
      ["boostBy":protected]=>
      NULL
    }
    [3]=>
    object(Gdbots\QueryParser\Node\Word)#590 (5) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "d"
      ["excluded":protected]=>
      bool(false)
      ["included":protected]=>
      bool(true)
      ["boostBy":protected]=>
      NULL
    }
    [4]=>
    object(Gdbots\QueryParser\Node\Word)#595 (5) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "6"
      ["excluded":protected]=>
      bool(false)
      ["included":protected]=>
      bool(true)
      ["boostBy":protected]=>
      NULL
    }
  }
  ["FILTER"]=>
  array(1) {
    [0]=>
    object(Gdbots\QueryParser\Node\ExplicitTerm)#594 (7) {
      ["nominator":protected]=>
      object(Gdbots\QueryParser\Node\Word)#591 (5) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "f"
        ["excluded":protected]=>
        bool(false)
        ["included":protected]=>
        bool(false)
        ["boostBy":protected]=>
        NULL
      }
      ["tokenType":protected]=>
      int(9)
      ["tokenTypeText":protected]=>
      string(1) ":"
      ["term":protected]=>
      object(Gdbots\QueryParser\Node\Word)#592 (5) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "v"
        ["excluded":protected]=>
        bool(false)
        ["included":protected]=>
        bool(false)
        ["boostBy":protected]=>
        NULL
      }
      ["excluded":protected]=>
      bool(false)
      ["included":protected]=>
      bool(false)
      ["boostBy":protected]=>
      string(3) "1.5"
    }
  }
}
*/

}
if ($parser->hasErrors()) {
    var_dump($parser->getErrors());
}
