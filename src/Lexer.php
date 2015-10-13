<?php
namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Token;

final class Lexer
{
    /**
     * Array of Tokens
     *
     * @var array
     */
    private static $tokens= [];

    /**
     * Private constructor. This class is not meant to be instantiated.
     */
    private function __construct() {}

    /**
     * Tokenizes search query string and returns search object.
     *
     * @param $str
     * @return array
     */
    public static function tokenize($str)
    {
        //$searchToken = new Searc();
        //$currentState = Parser::T_TERM;
        $len = strlen($str);

        $currentToken = new Token(Token::T_NONE, 0);
        $i = 0;
        while ($i < $len) {
            $char = $str[$i];
            switch ($char) {
                case ' ':
                   /* if ($currentState == Parser::T_LEFT_QUOTE) {
                        $currentToken .= $c;
                    }
                    else {
                        $currentToken = self::addToken($searchToken, $currentToken, $currentState);
                        $currentState = Parser::T_TERM;
                    }*/
                    $currentToken = self::pushToken($currentToken, $i);
                    break;
                case '@':
                    /*if ($currentToken != null || $currentState == Parser::T_LEFT_QUOTE) {
                        $currentToken .= $c;
                    } else {
                        $currentState = Parser::T_MENTION;
                    }*/
                    break;
                case '#':
                   /* if ($currentToken != null || $currentState == Parser::T_LEFT_QUOTE) {
                        $currentToken .= $c;
                    } else {
                        $currentState = Parser::T_HASHTAG;
                    }*/
                    break;
                case '"':
                    /*if ($currentState == Parser::T_LEFT_QUOTE) {
                        $currentState = Parser::T_PHRASE;
                        $currentToken = self::addToken($searchToken, $currentToken, $currentState);
                        $currentState = Parser::T_TERM;
                    } else {
                        if ($currentToken == null) {
                            $currentState = Parser::T_LEFT_QUOTE;
                        }
                        else {
                            //add current token and start new token with left quote state
                            $currentToken = self::addToken($searchToken, $currentToken, $currentState);
                            $currentState = Parser::T_LEFT_QUOTE;
                        }
                    }*/
                    $currentToken = self::tokenizeQuotedString($currentToken, $str, $i);
                    break;
                case '+':
                    /*if ($currentToken != null || $currentState == Parser::T_LEFT_QUOTE) {
                        $currentToken .= $c;
                    } else {
                        $currentState = Parser::T_INCLUDE;
                    }*/
                    break;
                case '-':
                    /*if ($currentToken != null || $currentState == Parser::T_LEFT_QUOTE) {
                        $currentToken .= $c;
                    } else {
                        $currentState = Parser::T_EXCLUDE;
                    }*/
                    break;
                default:
                    $currentToken->addData($char);
            }
            $i++;
        }

        $currentToken = self::pushToken($currentToken, $i);
        return self::$tokens;
    }

    /**
     * Adds token to array and creates new token
     *
     * @param array $tokens
     * @param Token $currentToken
     * @param int $i
     * @return Token
     */

    static private function pushToken($currentToken, $i)
    {
        if($currentToken->getData() === null) {
            $currentToken->setStartPosition($i);
            return;
        }
        $currentToken->setTypeIfNone(Token::T_TERM);
        self::$tokens[] = $currentToken;
        $currentToken = new Token(Token::T_NONE, $i);

        return $currentToken;
    }

    /**
     * Parses an quoted string
     *
     * @param Token $currentToken
     * @param string $string
     * @param int $i
     * @throws ParseException
     */
    static private function tokenizeQuotedString($currentToken, $string, &$i)
    {
        //check to see if ending quote
       /* if($currentToken->getType() == Token::T_QUOTE){
            $currentToken->setType(Token::T_PHRASE);
            $currentToken = self::pushToken($currentToken, $i);
        }
        else{*/
            if ($currentToken->getData() != null) {
                //add token you have now and begin the exact phrase
                $currentToken = self::pushToken($currentToken, $i);
            }
            //beginning quote
            $currentToken->setType(Token::T_PHRASE);

            //keep adding character to token until another quote or end of string
            while(++$i < strlen($string)) {
                if($string[$i] == '\\') {
                    $currentToken->addData($string[++$i]);
                } else if($string[$i] != '"') {
                    $currentToken->addData($string[$i]);
                } else {
                    break;
                }
            }
            $currentToken = self::pushToken($currentToken, $i);

        return $currentToken;

        /*if($currentToken->getData() == null) {
            $currentToken->setTypeIfNone(Token::T_STRING);
            self::readEncString($currentToken, $string, $i);
            if($i + 1 < strlen($string) && $string[$i + 1] != ' ') // Peek one ahead. Should be empty
                throw new ParseException('Unexpected T_STRING', $string, $i + 1);
        } else {
            throw new ParseException('Unexpected T_STRING', $string, $i);
        }*/

        /*if ($currentState == Parser::T_LEFT_QUOTE) {
                       $currentState = Parser::T_PHRASE;
                       $currentToken = self::addToken($searchToken, $currentToken, $currentState);
                       $currentState = Parser::T_TERM;
                   } else {
                       if ($currentToken == null) {
                           $currentState = Parser::T_LEFT_QUOTE;
                       }
                       else {
                           //add current token and start new token with left quote state
                           $currentToken = self::addToken($searchToken, $currentToken, $currentState);
                           $currentState = Parser::T_LEFT_QUOTE;
                       }
                   }*/



    }

    /**
     * Adds token to SearchToken object.
     *
     * @param SearchToken $searchToken
     * @param string $currentToken
     * @param int $currentState
     * @return null
     */

    /*static private function addToken($searchToken, $currentToken, $currentState)
    {
        if ($currentToken == null) {
            return;
        }

        switch ($currentState) {
            case Parser::T_TERM:
                $searchToken->terms[] = trim($currentToken);
                break;
            case Parser::T_MENTION:
                $searchToken->mentions[] = trim($currentToken);
                break;
            case Parser::T_HASHTAG:
                $searchToken->hashtags[] = trim($currentToken);
                break;
            case Parser::T_PHRASE:
                $searchToken->phrases[] = trim($currentToken);
                break;
            case Parser::T_LEFT_QUOTE:
                //never got closing quote so treat token as a phrase
                $searchToken->phrases[] = trim($currentToken);
                break;
            case Parser::T_INCLUDE:
                $searchToken->includes[] = trim($currentToken);
                break;
            case Parser::T_EXCLUDE:
                $searchToken->excludes[] = trim($currentToken);
                break;
        }
        return null;
    }*/

}
