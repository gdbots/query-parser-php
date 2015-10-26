<?php

require dirname(__DIR__) . '/../vendor/autoload.php';

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
> IncludeTerm
>> Word: b
> ExcludeTerm
>> Word: c
> IncludeTerm
>> Word: d
> Subexpression
>> Or
>>> Term: ^ 1.5
>>>> Term: f : v
>>> IncludeTerm
>>>> Word: 6
*/

    echo "\n\nCompiled query:\n\n";
    echo $parser->getScanner()->getProcessedData();

/*
a OR +b OR -c OR +d OR ( f:v^1.5 OR +6 )
*/

    echo "\n\nQuery object:\n\n";
    var_dump($query);

/*
object(Gdbots\QueryParser\Node\OrExpressionList)#598 (2) {
  ["expressions":protected]=>
  array(5) {
    [0]=>
    object(Gdbots\QueryParser\Node\Word)#582 (3) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "a"
      ["parentTokenTypes":protected]=>
      array(0) {
      }
    }
    [1]=>
    object(Gdbots\QueryParser\Node\IncludeTerm)#585 (2) {
      ["expression":protected]=>
      object(Gdbots\QueryParser\Node\Word)#584 (3) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "b"
        ["parentTokenTypes":protected]=>
        array(0) {
        }
      }
      ["parentTokenTypes":protected]=>
      array(0) {
      }
    }
    [2]=>
    object(Gdbots\QueryParser\Node\ExcludeTerm)#588 (2) {
      ["expression":protected]=>
      object(Gdbots\QueryParser\Node\Word)#586 (3) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "c"
        ["parentTokenTypes":protected]=>
        array(0) {
        }
      }
      ["parentTokenTypes":protected]=>
      array(0) {
      }
    }
    [3]=>
    object(Gdbots\QueryParser\Node\IncludeTerm)#589 (2) {
      ["expression":protected]=>
      object(Gdbots\QueryParser\Node\Word)#587 (3) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "d"
        ["parentTokenTypes":protected]=>
        array(0) {
        }
      }
      ["parentTokenTypes":protected]=>
      array(0) {
      }
    }
    [4]=>
    object(Gdbots\QueryParser\Node\SubExpression)#599 (2) {
      ["expression":protected]=>
      object(Gdbots\QueryParser\Node\OrExpressionList)#597 (2) {
        ["expressions":protected]=>
        array(2) {
          [0]=>
          object(Gdbots\QueryParser\Node\ExplicitTerm)#594 (5) {
            ["nominator":protected]=>
            object(Gdbots\QueryParser\Node\ExplicitTerm)#593 (5) {
              ["nominator":protected]=>
              object(Gdbots\QueryParser\Node\Word)#590 (3) {
                ["tokenType":protected]=>
                int(1)
                ["token":protected]=>
                string(1) "f"
                ["parentTokenTypes":protected]=>
                array(1) {
                  [9]=>
                  string(1) "v"
                }
              }
              ["tokenType":protected]=>
              int(9)
              ["tokenTypeText":protected]=>
              string(1) ":"
              ["term":protected]=>
              object(Gdbots\QueryParser\Node\Word)#591 (3) {
                ["tokenType":protected]=>
                int(1)
                ["token":protected]=>
                string(1) "v"
                ["parentTokenTypes":protected]=>
                array(0) {
                }
              }
              ["parentTokenTypes":protected]=>
              array(1) {
                [10]=>
                string(3) "1.5"
              }
            }
            ["tokenType":protected]=>
            int(10)
            ["tokenTypeText":protected]=>
            string(1) "^"
            ["term":protected]=>
            object(Gdbots\QueryParser\Node\Word)#592 (3) {
              ["tokenType":protected]=>
              int(1)
              ["token":protected]=>
              string(3) "1.5"
              ["parentTokenTypes":protected]=>
              array(0) {
              }
            }
            ["parentTokenTypes":protected]=>
            array(0) {
            }
          }
          [1]=>
          object(Gdbots\QueryParser\Node\IncludeTerm)#596 (2) {
            ["expression":protected]=>
            object(Gdbots\QueryParser\Node\Word)#595 (3) {
              ["tokenType":protected]=>
              int(1)
              ["token":protected]=>
              string(1) "6"
              ["parentTokenTypes":protected]=>
              array(0) {
              }
            }
            ["parentTokenTypes":protected]=>
            array(0) {
            }
          }
        }
        ["parentTokenTypes":protected]=>
        array(1) {
          [""]=>
          NULL
        }
      }
      ["parentTokenTypes":protected]=>
      array(0) {
      }
    }
  }
  ["parentTokenTypes":protected]=>
  array(0) {
  }
}
*/

    echo "\n\nGroup objects:\n\n";
    var_dump($query->getQueryItemsByTokenType());

/*
array(2) {
  ["WORD"]=>
  array(5) {
    [0]=>
    object(Gdbots\QueryParser\Node\Word)#582 (3) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "a"
      ["parentTokenTypes":protected]=>
      array(0) {
      }
    }
    [1]=>
    object(Gdbots\QueryParser\Node\Word)#584 (3) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "b"
      ["parentTokenTypes":protected]=>
      array(0) {
      }
    }
    [2]=>
    object(Gdbots\QueryParser\Node\Word)#586 (3) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "c"
      ["parentTokenTypes":protected]=>
      array(0) {
      }
    }
    [3]=>
    object(Gdbots\QueryParser\Node\Word)#587 (3) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "d"
      ["parentTokenTypes":protected]=>
      array(0) {
      }
    }
    [4]=>
    object(Gdbots\QueryParser\Node\Word)#595 (3) {
      ["tokenType":protected]=>
      int(1)
      ["token":protected]=>
      string(1) "6"
      ["parentTokenTypes":protected]=>
      array(0) {
      }
    }
  }
  ["FILTER"]=>
  array(1) {
    [0]=>
    object(Gdbots\QueryParser\Node\ExplicitTerm)#593 (5) {
      ["nominator":protected]=>
      object(Gdbots\QueryParser\Node\Word)#590 (3) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "f"
        ["parentTokenTypes":protected]=>
        array(1) {
          [9]=>
          string(1) "v"
        }
      }
      ["tokenType":protected]=>
      int(9)
      ["tokenTypeText":protected]=>
      string(1) ":"
      ["term":protected]=>
      object(Gdbots\QueryParser\Node\Word)#591 (3) {
        ["tokenType":protected]=>
        int(1)
        ["token":protected]=>
        string(1) "v"
        ["parentTokenTypes":protected]=>
        array(0) {
        }
      }
      ["parentTokenTypes":protected]=>
      array(1) {
        [10]=>
        string(3) "1.5"
      }
    }
  }
}
*/

}
if ($parser->hasErrors()) {
    var_dump($parser->getErrors());
}
