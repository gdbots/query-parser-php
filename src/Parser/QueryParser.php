<?php

namespace Gdbots\QueryParser\Parser;

use Gdbots\QueryParser\Node\Text;
use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\Node\ExplicitTerm;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\ExcludeTerm;
use Gdbots\QueryParser\Node\IncludeTerm;
use Gdbots\QueryParser\Node\OrExpressionList;
use Gdbots\QueryParser\Node\AndExpressionList;
use Gdbots\QueryParser\Node\SubExpression;

/**
 * A parser using the QueryScanner as input for tokens. The parser builds a
 * a query tree, representing the query expression delivered as input.
 * The parser returns a Query Tree if the parsing is successful or null if it failed.
 * The parser will try to parse the entire input string and delivers feedback on errors,
 * for each expression it could not parse.
 */
class QueryParser
{
    /**
     * @var QueryScanner
     */
    protected $scanner;

    /**
     * An array containing strings with an error message for every expression
     * that could not be parsed.
     *
     * @var array
     */
    protected $feedback = array();

    /**
     * __construct
     */
    public function __construct()
    {
        $this->scanner = new QueryScanner();
    }

    /**
     * Resets the input string and feedback.
     *
     * @param string $input
     */
    public function readString($input)
    {
        $this->scanner->readString($input);
        $this->feedback = array();
    }

    /**
     * Private function to add an error message to the feedback array.
     *
     * @param string $input
     */
    protected function addFeedback($message)
    {
        $this->feedback[] = $message;
    }

    /**
     * Checks if the parser has any feedback to deliver.
     *
     * @return bool
     */
    public function hasFeedback()
    {
        return count($this->feedback) > 0;
    }

    /**
     * Returns an array with feedback (error) messages from the parser.
     *
     * @return bool
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * Makes the parser read a single term. This can be a word, text, or explicit term.
     *
     * @param int    $tokenType
     * @param string $word
     *
     * @return \Gdbots\QueryParser\Node\QueryItem|null
     */
    protected function readTerm($tokenType, $word)
    {
        if (!in_array($tokenType, array(QueryScanner::T_COLON, QueryScanner::T_BOOST))) {
            return $word;
        }

        $value = null;

        switch ($this->scanner->next()) {
            case QueryScanner::T_TEXT:
                $value = new Text($this->scanner->getToken());

                break;

            case QueryScanner::T_WORD:
                $value = new Word($this->scanner->getToken());

                break;

            default:
                $this->addFeedback(sprintf('Error: Expected Word or Text. Found: "%s"', $this->scanner->getTokenTypeText()));

                return null;
        }

        $this->scanner->next();

        return new ExplicitTerm($word, $tokenType, $value);
    }

    /**
     * Makes the parser read an expression. This can be:
     * - '(' Subexpression ')'
     * - '"' text '"'
     * - Word
     * - Word:Text
     * - Word:Word
     * - '-' Expression
     * - '+' Expression
     * - '#' Expression
     * - '@' Expression
     *
     * @param int $tokenType
     *
     * @return \Gdbots\QueryParser\Node\QueryItem|null
     */
    protected function readExpression($tokenType)
    {
        switch ($tokenType) {
            case QueryScanner::T_LPAREN:
                return $this->readSubQuery($tokenType);

            case QueryScanner::T_TEXT:
                $text = new Text($this->scanner->getToken());

                $this->scanner->next();

                return $text;

            case QueryScanner::T_WORD:
                $word =  new Word($this->scanner->getToken());

                return $this->readTerm($this->scanner->next(), $word);

            case QueryScanner::T_EXCLUDE:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    return new ExcludeTerm($expression);
                } else {
                    $this->addFeedback('Error: EXCLUDE not followed by a valid expression.');
                }

                break;

            case QueryScanner::T_INCLUDE:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    return new IncludeTerm($expression);
                } else {
                    $this->addFeedback('Error: INCLUDE not followed by a valid expression.');
                }

                break;

            case QueryScanner::T_HASHTAG:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    return new Hashtag($expression);
                } else {
                    $this->addFeedback('Error: HASHTAG not followed by a valid expression.');
                }

                break;

            case QueryScanner::T_MENTION:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    return new Mention($expression);
                } else {
                    $this->addFeedback('Error: MENTION not followed by a valid expression.');
                }

                break;

            case QueryScanner::T_ILLEGAL:
                $this->addFeedback(sprintf(
                    'Error: Expected Expression. Found illegal character: "%s"',
                    $this->scanner->getTokenTypeText()
                ));

                break;

            case QueryScanner::T_QUOTE:
                $this->addFeedback(sprintf(
                    'Error: Opening quote at pos %s lacks closing quote: "%s"',
                    $this->scanner->getPosition(),
                    $this->scanner->getToken()
                ));

                break;

            case QueryScanner::T_OR_OPERATOR:
                $this->addFeedback(sprintf(
                    'Error: Expected Expression. OR operator found at pos %s "%s" remaining: "%s"',
                    $this->scanner->getPosition(),
                    $this->scanner->getToken(),
                    $this->scanner->getRemainingData()
                ));

                break;

            case QueryScanner::T_AND_OPERATOR:
                $this->addFeedback(sprintf(
                    'Error: Expected Expression. AND operator found at pos %s "%s" remaining: "%s"',
                    $this->scanner->getPosition(),
                    $this->scanner->getToken(),
                    $this->scanner->getRemainingData()
                ));

                break;
        }

        return null;
    }

    /**
     * Makes the parser read a list of OR statements. This can be:
     * - Expression
     * - Expression OR Expression OR ...
     *
     * @param int $tokenType
     *
     * @return \Gdbots\QueryParser\Node\QueryItem|null
     */
    protected function readOrExpressionList($tokenType)
    {
        $expressions = array();
        $lastExpression = false;

        do {
            $lastExpression =  $this->readExpression($this->scanner->getTokenType());
            $expressions[] = $lastExpression;

        } while ($lastExpression && $this->scanner->getTokenType() == QueryScanner::T_OR_OPERATOR && $this->scanner->next());

        if ($lastExpression) {
            if (sizeof($expressions) === 1) {
                return $expressions[0];
            } else {
                return new OrExpressionList($expressions);
            }
        }

        return null;
    }

    /**
     * Makes the parser read a list of statements. This can be:
     * - Expression
     * - Expression Expression ...
     *
     * @param int $tokenType
     *
     * @return \Gdbots\QueryParser\Node\QueryItem|null
     */
    protected function readAndExpressionList($tokenType)
    {
        $expressions = array();
        $lastExpression = false;

        do {
            $lastExpression = $this->readOrExpressionList($this->scanner->getTokenType());
            $expressions[] = $lastExpression;

        } while ($lastExpression && $this->scanner->getTokenType() == QueryScanner::T_AND_OPERATOR && $this->scanner->next());

        switch ($this->scanner->getTokenType()) {
            case QueryScanner::T_RPAREN:
            case QueryScanner::T_EOL:
                if (sizeof($expressions) === 1) {
                    return $expressions[0];
                } else {
                    return new AndExpressionList($expressions);
                }
        }

        $this->addFeedback(sprintf('Error: Expected Expression. Found: "%s"', $this->scanner->getTokenTypeText()));

        return null;
    }

    /**
     * Makes the parser read paren closed (sub)query. The passed token should be the left paren.
     *
     * @param int $tokenType
     *
     * @return \Gdbots\QueryParser\Node\QueryItem|null
     */
    protected function readSubQuery($tokenType)
    {
        $expressionlist = $this->readAndExpressionList($this->scanner->next());
        if ($this->scanner->getTokenType() == QueryScanner::T_RPAREN) {
            $this->scanner->next();
            return new SubExpression($expressionlist);
        }

        $this->addFeedback('Error: Expected Right Paren.');

        return null;
    }

    /**
     * Makes the parser build an expression tree from the given input.
     *
     * @return \Gdbots\QueryParser\Node\QueryItem|null
     */
    public function parse()
    {
        return $this->readAndExpressionList($this->scanner->next());
    }
}
