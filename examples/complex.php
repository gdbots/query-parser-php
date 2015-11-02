<?php

require dirname(__DIR__) . '/../vendor/autoload.php';

use Gdbots\QueryParser\QueryParser;
use Gdbots\QueryParser\Visitor\QueryItemPrinter;

$parser = new QueryParser();

if ($query = $parser->parse('a+b -c +d (f:v^1.5+6)', false)) {
    echo "Print output:\n\n";
    $query->accept(new QueryItemPrinter());

/*
 Or
> Word (WORD): a+b
> Word (WORD): -c
> Word (WORD): +d
> Subexpression
>> Or
>>> Term: f : v ^ 1.50
>>> Word (NUMBER): +6
*/

    echo "\n\nCompiled query:\n\n";
    echo $parser->getLexer()->getProcessedData();

/*
a+b OR -c OR +d OR ( f:v^1.5 OR +6 )
*/

    echo "\n\nQuery object:\n\n";
    var_dump($query);

/*
object(Gdbots\QueryParser\Node\OrExpressionList)#596 (4) {
  ["expressions":protected]=>
  array(4) {
    [0]=>
    object(Gdbots\QueryParser\Node\Word)#586 (5) {
      ["tokenType":protected]=>
      int(2)
      ["token":protected]=>
      string(3) "a+b"
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
      int(2)
      ["token":protected]=>
      string(1) "c"
      ["excluded":protected]=>
      bool(true)
      ["included":protected]=>
      bool(false)
      ["boostBy":protected]=>
      NULL
    }
    [2]=>
    object(Gdbots\QueryParser\Node\Word)#589 (5) {
      ["tokenType":protected]=>
      int(2)
      ["token":protected]=>
      string(1) "d"
      ["excluded":protected]=>
      bool(false)
      ["included":protected]=>
      bool(true)
      ["boostBy":protected]=>
      NULL
    }
    [3]=>
    object(Gdbots\QueryParser\Node\SubExpression)#592 (4) {
      ["expression":protected]=>
      object(Gdbots\QueryParser\Node\OrExpressionList)#595 (4) {
        ["expressions":protected]=>
        array(2) {
          [0]=>
          object(Gdbots\QueryParser\Node\ExplicitTerm)#593 (7) {
            ["nominator":protected]=>
            object(Gdbots\QueryParser\Node\Word)#590 (5) {
              ["tokenType":protected]=>
              int(2)
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
            int(20)
            ["tokenTypeText":protected]=>
            string(1) ":"
            ["term":protected]=>
            object(Gdbots\QueryParser\Node\Word)#591 (5) {
              ["tokenType":protected]=>
              int(2)
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
          object(Gdbots\QueryParser\Node\Word)#594 (5) {
            ["tokenType":protected]=>
            int(6)
            ["token":protected]=>
            string(2) "+6"
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
array(3) {
  ["WORD"]=>
  array(3) {
    [0]=>
    object(Gdbots\QueryParser\Node\Word)#586 (5) {
      ["tokenType":protected]=>
      int(2)
      ["token":protected]=>
      string(3) "a+b"
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
      int(2)
      ["token":protected]=>
      string(1) "c"
      ["excluded":protected]=>
      bool(true)
      ["included":protected]=>
      bool(false)
      ["boostBy":protected]=>
      NULL
    }
    [2]=>
    object(Gdbots\QueryParser\Node\Word)#589 (5) {
      ["tokenType":protected]=>
      int(2)
      ["token":protected]=>
      string(1) "d"
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
    object(Gdbots\QueryParser\Node\ExplicitTerm)#593 (7) {
      ["nominator":protected]=>
      object(Gdbots\QueryParser\Node\Word)#590 (5) {
        ["tokenType":protected]=>
        int(2)
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
      int(20)
      ["tokenTypeText":protected]=>
      string(1) ":"
      ["term":protected]=>
      object(Gdbots\QueryParser\Node\Word)#591 (5) {
        ["tokenType":protected]=>
        int(2)
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
  ["NUMBER"]=>
  array(1) {
    [0]=>
    object(Gdbots\QueryParser\Node\Word)#594 (5) {
      ["tokenType":protected]=>
      int(6)
      ["token":protected]=>
      string(2) "+6"
      ["excluded":protected]=>
      bool(false)
      ["included":protected]=>
      bool(false)
      ["boostBy":protected]=>
      NULL
    }
  }
}
*/

}
