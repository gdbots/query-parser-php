<?php
namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Token;
use Gdbots\Common\Util\HashtagUtils;

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
        $len = strlen($str);
        self::$tokens = [];

        $currentToken = new Token(Token::T_NONE, 0);
        $i = 0;
        while ($i < $len) {
            $char = substr($str, $i, 1);
            switch ($char) {
                case ' ':
                    $currentToken = self::pushToken($currentToken, $i);
                    break;
                case '@':
                    $currentToken = self::tokenizeMention($currentToken, $i);
                    break;
                case '#':
                    $currentToken = self::tokenizeHashtag($currentToken, $i);
                    break;
                case '"':
                    $currentToken = self::tokenizeQuotedString($currentToken, $str, $i);
                    break;
                case '+':
                    self::tokenizeInclude($currentToken, $str, $i);
                    break;
                case '-':
                    self::tokenizeExclude($currentToken, $str, $i);
                    break;
                case '^':
                    $currentToken = self::tokenizeBoost($currentToken, $str, $i);
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
     * @param Token $currentToken
     * @param int $i
     * @return Token
     */

    static private function pushToken($currentToken, $i)
    {

        if ($currentToken->getData() === null) {
            //if state is in phrase then the current token was an empty quoted string and does not need to be added
            if ($currentToken->getType() === Token::T_PHRASE) {
                $currentToken = new Token(Token::T_NONE, $i);
            }
            //$currentToken->setStartPosition($i);
            return $currentToken;
        }
        //if adding hashtag then use extract to cleanup hashtag
        if ($currentToken->getType() === Token::T_HASHTAG) {
            $hashtagArray = HashtagUtils::extract('#'.$currentToken->getData());
            $currentToken->setData($hashtagArray[0]);
            self::$tokens[] = $currentToken;
            /*foreach($hashtagArray as $hashTag) {
                $currentToken = new Token(Token::T_HASHTAG, $i);
                $currentToken->setData($hashTag);
                self::$tokens[] = $currentToken;
            }*/
        } else {
            $currentToken->setTypeIfNone(Token::T_KEYWORD);
            self::$tokens[] = $currentToken;
        }
        $currentToken = new Token(Token::T_NONE, $i);

        return $currentToken;
    }


    /**
     * Parses a Boost
     *
     * @param Token $currentToken
     * @param string $string
     * @param int $i
     * @return Token $currentToken
     * @throws ParseException
     */
    static private function tokenizeBoost($currentToken, $string, &$i)
    {
        if ($currentToken->getData() != null) {
            //only boost keywords/terms for now
            if ($currentToken->getType() === Token::T_KEYWORD || $currentToken->getType() === Token::T_NONE || $currentToken->getType() === Token::T_PHRASE || $currentToken->getType() === Token::T_HASHTAG ||  $currentToken->getType() === Token::T_MENTION) {
                //grab the boost value
                $boostValue = self::readInt($string, $i);
                if (!empty($boostValue)) {
                    //change type to boost and set value to current token
                    $currentToken->setBoost($boostValue);
                    //if boosting a phrase just set the boost value and have quoted string tokenizer add the token
                    if ($currentToken->getType() !== Token::T_PHRASE) {
                        $currentToken = self::pushToken($currentToken, $i);
                    }
                }
            }

        }
        return $currentToken;
    }

    /**
     * Parses a hashtag
     * @param Token $currentToken
     * @param string $string
     * @param int $i
     * @return Token $currentToken
     */
    static private function tokenizeHashtag($currentToken, $i)
    {
        if ($currentToken->getData() != null) {
            $currentToken = self::pushToken($currentToken, $i);
        } else {
            $currentToken->setStartPosition($i);
        }
        $currentToken->setType(Token::T_HASHTAG);
        return $currentToken;
    }

    /**
     * Parses a mention
     * @param Token $currentToken
     * @param string $string
     * @param int $i
     * @return Token $currentToken
     */
    static private function tokenizeMention($currentToken, $i)
    {
        if ($currentToken->getData() != null) {
            $currentToken = self::pushToken($currentToken, $i);
        } else {
            $currentToken->setStartPosition($i);
        }
        $currentToken->setType(Token::T_MENTION);
        return $currentToken;

    }

    /**
     * Parses an exclude
     * @param Token $currentToken
     * @param string $string
     * @param int $i
     */
    static private function tokenizeExclude($currentToken, $string, $i)
    {
        if ($currentToken->getData() != null){
            $currentToken->addData(substr($string, $i, 1));

        } else {
            $currentToken->setStartPosition($i);
            $currentToken->setType(Token::T_EXCLUDE);
        }
    }

    /**
     * Parses an include
     * @param Token $currentToken
     * @param string $string
     * @param int $i
     */
    static private function tokenizeInclude($currentToken, $string, $i)
    {
        if ($currentToken->getData() != null){
            $currentToken->addData(substr($string, $i, 1));

        } else {
            $currentToken->setStartPosition($i);
            $currentToken->setType(Token::T_INCLUDE);
        }
    }


    /**
     * Parses an quoted string
     *
     * @param Token $currentToken
     * @param string $string
     * @param int $i
     * @return Token $currentToken
     * @throws ParseException
     */
    static private function tokenizeQuotedString($currentToken, $string, &$i)
    {
        if ($currentToken->getData() != null) {
            //add token you have now and begin the exact phrase
            $currentToken = self::pushToken($currentToken, $i);
        }
        //beginning quote
        $currentToken->setType(Token::T_PHRASE);

        //keep adding character to token until another quote or end of string
        while(++$i < strlen($string)) {
            if (substr($string, $i, 1) == '\\') {
                $currentToken->addData(substr($string, ++$i, 1));
            } else if (substr($string, $i, 1) != '"') {
                $currentToken->addData(substr($string, $i, 1));
            } else {
                //check to see if this phrase has a boost by looking ahead
                if (substr($string, $i+1, 1) == '^') {
                    self::tokenizeBoost($currentToken, $string, ++$i);
                }
                break;
            }
        }
        $currentToken = self::pushToken($currentToken, $i);

        return $currentToken;
    }

    /**
     * Reads an integer
     * @param Token $currentToken
     * @param string $string The string being tokenized
     * @param int $i The current position in the string being tokenized
     * @return string value
     */
    static private function readInt($string, &$i)
    {
        $value= null;

        while (++$i < strlen($string)) {
            if (in_array(substr($string, $i, 1), array('0', '1', '2', '3', '4', '5', '6', '7',
                '8', '9'), true)) {
                $value .= (substr($string, $i, 1));
            } else {
                $i--;
                break;
            }
        }

        return $value;
    }


}
