<?php

namespace Gdbots\QueryParser\Parser;

use Gdbots\QueryParser\Node;

/**
 * A parser using the QueryScanner as input for tokens. The parser builds a
 * a query tree, representing the query expression delivered as input.
 * The parser returns a Query Tree if the parsing is successful or null if it failed.
 * The parser will try to parse the entire input string and delivers errors,
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
    protected $errors = [];

    /**
     * __construct
     */
    public function __construct()
    {
        $this->scanner = new QueryScanner();
    }

    /**
     * Returns scanner instance.
     *
     * @return QueryScanner
     */
    public function getScanner()
    {
        return $this->scanner;
    }

    /**
     * Resets the input string and errors.
     *
     * @param string $input
     * @param bool   $ignoreOperator
     */
    public function readString($input, $ignoreOperator = false)
    {
        $this->scanner->readString($input, $ignoreOperator);
        $this->errors = [];
    }

    /**
     * Private function to add an error message to the errors array.
     *
     * @param string $input
     */
    protected function addError($message)
    {
        $this->errors[] = $message;
    }

    /**
     * Checks if the parser has any errors to deliver.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * Returns an array with error messages from the parser.
     *
     * @return bool
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Makes the parser read a single term. This can be a word, text, or explicit term.
     *
     * @param int    $tokenType
     * @param string $term
     *
     * @return \Gdbots\QueryParser\Node\QueryItem|null
     */
    protected function readTerm($tokenType, $term)
    {
        if (!in_array($tokenType, [QueryScanner::T_FILTER, QueryScanner::T_BOOST])) {
            return $term;
        }

        if ($tokenType == QueryScanner::T_FILTER && $term->getTokenType() == QueryScanner::T_PHRASE) {
            $this->addError(sprintf('Error: FILTER only support Word. Found: "%s"', $this->scanner->getTokenTypeText()));

            return $term;
        }

        $value = null;
        $tokenTypeText = $this->scanner->getToken();

        switch ($this->scanner->next()) {
            case QueryScanner::T_PHRASE:
                $value = new Node\Phrase($this->scanner->getToken());

                break;

            case QueryScanner::T_WORD:
                $value = new Node\Word($this->scanner->getToken());

                break;

            case QueryScanner::T_URL:
                $value = new Node\Url($this->scanner->getToken());

                break;

            default:
                $this->addError(sprintf('Error: Expected Word or Phrase. Found: "%s"', $this->scanner->getTokenTypeText()));

                return null;
        }

        $this->scanner->next();

        return new Node\ExplicitTerm($term, $tokenType, $tokenTypeText, $value);
    }

    /**
     * Makes the parser read an expression. This can be:
     * - '(' Subexpression ')'
     * - '"' text '"'
     * - Word
     * - Word:Phrase
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
            case QueryScanner::T_OPEN_PARENTHESIS:
                return $this->readSubQuery($tokenType);

            case QueryScanner::T_PHRASE:
                $text = new Node\Phrase($this->scanner->getToken());
                return $this->readTerm($this->scanner->next(), $text);

            case QueryScanner::T_WORD:
                $word = new Node\Word($this->scanner->getToken());
                return $this->readTerm($this->scanner->next(), $word);

            case QueryScanner::T_URL:
                $url = new Node\Url($this->scanner->getToken());
                return $this->readTerm($this->scanner->next(), $url);

            case QueryScanner::T_EXCLUDE:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    if ($expression->getTokenType() == QueryScanner::T_BOOST) {
                        $term = new Node\ExcludeTerm($expression->getNominator());
                        return new Node\ExplicitTerm($term, $expression->getTokenType(), '^', $expression->getTerm());
                    } else {
                        return new Node\ExcludeTerm($expression);
                    }
                } else {
                    $this->addError('Error: EXCLUDE not followed by a valid expression.');
                }

                break;

            case QueryScanner::T_INCLUDE:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    if ($expression->getTokenType() == QueryScanner::T_BOOST) {
                        $term = new Node\IncludeTerm($expression->getNominator());
                        return new Node\ExplicitTerm($term, $expression->getTokenType(), '^', $expression->getTerm());
                    } else {
                        return new Node\IncludeTerm($expression);
                    }
                } else {
                    $this->addError('Error: INCLUDE not followed by a valid expression.');
                }

                break;

            case QueryScanner::T_HASHTAG:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    if ($expression->getTokenType() == QueryScanner::T_BOOST) {
                        $term = new Node\Hashtag($expression->getNominator());
                        return new Node\ExplicitTerm($term, $expression->getTokenType(), '^', $expression->getTerm());
                    } else {
                        return new Node\Hashtag($expression);
                    }
                } else {
                    $this->addError('Error: HASHTAG not followed by a valid expression.');
                }

                break;

            case QueryScanner::T_MENTION:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    if ($expression->getTokenType() == QueryScanner::T_BOOST) {
                        $term = new Node\Mention($expression->getNominator());
                        return new Node\ExplicitTerm($term, $expression->getTokenType(), '^', $expression->getTerm());
                    } else {
                        return new Node\Mention($expression);
                    }
                } else {
                    $this->addError('Error: MENTION not followed by a valid expression.');
                }

                break;

            case QueryScanner::T_ILLEGAL:
                $this->addError(sprintf(
                    'Error: Expected Expression. Found illegal character: "%s"',
                    $this->scanner->getTokenTypeText()
                ));

                break;

            case QueryScanner::T_QUOTE:
                $this->addError(sprintf(
                    'Error: Opening quote at pos %s lacks closing quote: "%s"',
                    $this->scanner->getPosition(),
                    $this->scanner->getToken()
                ));

                break;

            case QueryScanner::T_OR_OPERATOR:
                $this->addError(sprintf(
                    'Error: Expected Expression. OR operator found at pos %s "%s" remaining: "%s"',
                    $this->scanner->getPosition(),
                    $this->scanner->getToken(),
                    $this->scanner->getRemainingData()
                ));

                break;

            case QueryScanner::T_AND_OPERATOR:
                $this->addError(sprintf(
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
        $expressions = [];
        $lastExpression = false;

        do {
            $lastExpression = $this->readExpression($this->scanner->getTokenType());

            if ($this->scanner->getTokenType() == QueryScanner::T_BOOST) {
                $expression = $this->readExpression($this->scanner->next());
                $lastExpression = new Node\ExplicitTerm($lastExpression, QueryScanner::T_BOOST, '^', $expression);
            }

            $expressions[] = $lastExpression;

        } while ($lastExpression && $this->scanner->getTokenType() == QueryScanner::T_OR_OPERATOR && $this->scanner->next());

        if ($lastExpression) {
            if (count($expressions) === 1) {
                return $expressions[0];
            } else {
                return new Node\OrExpressionList($expressions);
            }
        }

        return null;
    }

    /**
     * Makes the parser read a list of statements. This can be:
     * - Expression
     * - Expression AND Expression AND ...
     *
     * @return \Gdbots\QueryParser\Node\QueryItem|null
     */
    protected function readAndExpressionList()
    {
        $expressions = [];
        $lastExpression = false;

        do {
            $lastExpression = $this->readOrExpressionList($this->scanner->getTokenType());
            $expressions[] = $lastExpression;

        } while ($lastExpression && $this->scanner->getTokenType() == QueryScanner::T_AND_OPERATOR && $this->scanner->next());

        switch ($this->scanner->getTokenType()) {
            case QueryScanner::T_CLOSE_PARENTHESIS:
            case QueryScanner::T_EOI:
                if (count($expressions) === 1) {
                    return $expressions[0];
                } else {
                    return new Node\AndExpressionList($expressions);
                }
        }

        $this->addError(sprintf('Error: Expected Expression. Found: "%s"', $this->scanner->getTokenTypeText()));

        return null;
    }

    /**
     * Makes the parser read expressions between parentheses.
     *
     * @return \Gdbots\QueryParser\Node\QueryItem|null
     */
    protected function readSubQuery()
    {
        $expressionlist = $this->readAndExpressionList($this->scanner->next());
        if ($this->scanner->getTokenType() == QueryScanner::T_CLOSE_PARENTHESIS) {
            $this->scanner->next();

            if (!($expressionlist instanceof Node\ExpressionList)) {
                return $expressionlist;
            }

            return new Node\SubExpression($expressionlist);
        }

        $this->addError('Error: Expected `)` but end of stream reached.');

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
