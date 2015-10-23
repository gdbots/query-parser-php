<?php

namespace Gdbots\Tests\QueryParser;

use Gdbots\QueryParser\Lexer;
use Gdbots\QueryParser\Token;

class LexerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Token */
    protected $token;

    /*public function setUp()
    {
        $this->parser = new Token();
        $this->printer = new QueryItemPrinter();
    }*/

    public function tearDown()
    {
        $this->parser = null;
        $this->printer = null;
    }

    public function queryDataprovider()
    {
        return [
            ['"dude"^8"dude2 ^hehe"brian^arse^8hsiao^^99 #hashtag^2@mention^2',
                array(  array('type'=>Token::T_PHRASE,'data'=>'dude','boost'=>8),
                        array('type'=>Token::T_PHRASE,'data'=>'dude2 ^hehe'),
                        array('type'=>Token::T_KEYWORD,'data'=>'brian^arse','boost'=>8),
                        array('type'=>Token::T_KEYWORD,'data'=>'hsiao','boost'=>99),
                        array('type'=>Token::T_HASHTAG,'data'=>'hashtag','boost'=>2),
                        array('type'=>Token::T_MENTION,'data'=>'mention','boost'=>2)
                )],
            ['#+hashtag1 #-hashtag2',
                array(  array('type'=>Token::T_HASHTAG,'data'=>'hashtag1'),
                        array('type'=>Token::T_HASHTAG,'data'=>'hashtag2')
                )],
        ];
    }

    /**
     * @dataProvider queryDataprovider
     */
    public function testLexerTokenize($query, $expected)
    {
        $expectedTokenArray = array();
        foreach ($expected as $token) {
            $t = new Token($token['type'], null);
            if (isset($token['data'])) {
                $t->setData($token['data']);
            }
            if (isset($token['boost'])) {
                $t->setBoost($token['boost']);
            }
            if (isset($token['value'])) {
                $t->setvalue($token['value']);
            }
            $expectedTokenArray[] = $t;
            $t= null;
        }

        $tokenArray = Lexer::tokenize($query);

        $this->AssertEquals(json_encode($expectedTokenArray), json_encode($tokenArray));
    }


    /*public function testIsValidBoost()
    {
        $queryString = '"dude"^8"dude2 ^hehe"brian^arse^8hsiao^^99 #hashtag^2@mention^2';
        $tokenArray = array();

        $tokenArray = Lexer::tokenize($queryString);

        $this->AssertEquals("8", $tokenArray[0]->getBoost());
        $this->AssertEquals("dude", $tokenArray[0]->getData());
        $this->AssertEquals("dude2 ^hehe", $tokenArray[1]->getData());
        $this->AssertNull($tokenArray[1]->getBoost());
        $this->AssertEquals("8", $tokenArray[2]->getBoost());
        $this->AssertEquals("brianarse", $tokenArray[2]->getData());
        $this->AssertEquals("99", $tokenArray[3]->getBoost());
        $this->AssertEquals("hsiao", $tokenArray[3]->getData());
        $this->AssertEquals("2", $tokenArray[4]->getBoost());
        $this->AssertEquals("hashtag", $tokenArray[4]->getData());
        $this->AssertEquals(\Gdbots\QueryParser\Token::T_HASHTAG, $tokenArray[4]->getType());
        $this->AssertEquals("2", $tokenArray[5]->getBoost());
        $this->AssertEquals("mention", $tokenArray[5]->getData());
        $this->AssertEquals(\Gdbots\QueryParser\Token::T_MENTION, $tokenArray[5]->getType());

    }

    public function testIsValidBoostWithNonDigitAndDigitBoost()
    {
        $queryString = 'two^1abc';

        $tokenArray = Lexer::tokenize($queryString);

        $this->AssertEquals("1", $tokenArray[0]->getBoost());
        $this->AssertEquals("two", $tokenArray[0]->getData());
        $this->AssertEquals(\Gdbots\QueryParser\Token::T_KEYWORD, $tokenArray[0]->getType());
        $this->AssertEquals("abc", $tokenArray[1]->getData());
        $this->AssertEquals(\Gdbots\QueryParser\Token::T_KEYWORD, $tokenArray[1]->getType());
    }

    public function testIsValidBoostWithNonDigitBoost()
    {
        $queryString = 'one^two';

        $tokenArray = Lexer::tokenize($queryString);

        $this->AssertEquals("onetwo", $tokenArray[0]->getData());
        $this->AssertEquals(\Gdbots\QueryParser\Token::T_KEYWORD, $tokenArray[0]->getType());
        $this->AssertNull($tokenArray[0]->getBoost());

    }*/
}
