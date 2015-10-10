<?php
namespace Gdbots\QueryParser;

class Parser
{

    const T_TERM        = 1;
    const T_MENTION     = 2;
    const T_HASHTAG     = 3;
    const T_LEFT_QUOTE  = 4;
    const T_PHRASE      = 5;
    const T_INCLUDE     = 6;
    const T_EXCLUDE     = 7;

    /**
     * Private constructor. This class is not meant to be instantiated.
     */
    private function __construct() {}

    /**
     * Tokenizes search query string and returns search object.
     *
     * @param $str
     * @return SearchToken
     */
    public static function tokenize($str)
    {
        $searchToken = new SearchToken();
        $currentState = Parser::T_TERM;
        $currentToken = null;

        $len = strlen($str);
        $i = 0;
        while ($i < $len) {
            $c = $str[$i];
            switch ($c) {
                case ' ':
                    if ($currentState == Parser::T_LEFT_QUOTE) {
                        $currentToken .= $c;
                    }
                    else {
                        $currentToken = self::addToken($searchToken, $currentToken, $currentState);
                        $currentState = Parser::T_TERM;
                    }
                    break;
                case '@':
                    if ($currentToken != null || $currentState == Parser::T_LEFT_QUOTE) {
                        $currentToken .= $c;
                    } else {
                        $currentState = Parser::T_MENTION;
                    }
                    break;
                case '#':
                    if ($currentToken != null || $currentState == Parser::T_LEFT_QUOTE) {
                        $currentToken .= $c;
                    } else {
                        $currentState = Parser::T_HASHTAG;
                    }
                    break;
                case '"':
                    if ($currentState == Parser::T_LEFT_QUOTE) {
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
                    }
                    break;
                case '+':
                    if ($currentToken != null || $currentState == Parser::T_LEFT_QUOTE) {
                        $currentToken .= $c;
                    } else {
                        $currentState = Parser::T_INCLUDE;
                    }
                    break;
                case '-':
                    if ($currentToken != null || $currentState == Parser::T_LEFT_QUOTE) {
                        $currentToken .= $c;
                    } else {
                        $currentState = Parser::T_EXCLUDE;
                    }
                    break;
                default:
                    $currentToken .= $c;
            }
            $i++;
        }

        self::addToken($searchToken, $currentToken, $currentState);
        return $searchToken;
    }

    /**
     * Adds token to SearchToken object.
     *
     * @param SearchToken $searchToken
     * @param string $currentToken
     * @param int $currentState
     * @return null
     */

    static private function addToken($searchToken, $currentToken, $currentState)
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
    }

}
