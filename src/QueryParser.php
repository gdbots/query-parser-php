<?php

namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Node;

/**
 * A parser using the QueryLexer as input for tokens. The parser builds a
 * a query tree, representing the query expression delivered as input.
 * The parser returns a Query Tree if the parsing is successful or null if it failed.
 * The parser will try to parse the entire input string and delivers errors,
 * for each expression it could not parse.
 */
class QueryParser
{
    /**
     * @var QueryLexer
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
        $this->scanner = new QueryLexer();
    }

    /**
     * Returns scanner instance.
     *
     * @return QueryLexer
     */
    public function getLexer()
    {
        return $this->scanner;
    }

    /**
     * Makes the parser build an expression tree from the given input.
     *
     * @param string $input
     * @param bool   $ignoreOperator
     *
     * @return \Gdbots\QueryParser\Node\AbstractQueryItem|null
     */
    public function parse($input, $ignoreOperator = false)
    {
        $this->scanner->readString($input, $ignoreOperator);
        $this->errors = [];

        return $this->readAndExpressionList();
    }

    /**
     * Private function to add an error message to the errors array.
     *
     * @param string $message
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
     * @return array
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
     * @return \Gdbots\QueryParser\Node\AbstractQueryItem|null
     */
    protected function readTerm($tokenType, $term)
    {
        if (!in_array($tokenType, [QueryLexer::T_FILTER])) {
            return $term;
        }

        if (in_array($tokenType, [QueryLexer::T_FILTER]) && $term->getTokenType() != QueryLexer::T_WORD) {
            $this->addError(sprintf(
                'Error: FILTER only support Word. Found: "%s"',
                $this->scanner->getTokenTypeText()
            ));

            return $term;
        }

        $value = null;
        $tokenTypeText = $this->scanner->getToken();

        switch ($this->scanner->next()) {
            case QueryLexer::T_WORD:
            case QueryLexer::T_DATE:
            case QueryLexer::T_NUMBER:
            case QueryLexer::T_URL:
                $value = new Node\Word($this->scanner->getToken(), $this->scanner->getTokenType());

                break;

            case QueryLexer::T_PHRASE:
                $value = new Node\Phrase($this->scanner->getToken());

                break;

            default:
                $this->addError(sprintf(
                    'Error: Expected Word, Phrase, or Url. Found: "%s"',
                    $this->scanner->getTokenTypeText()
                ));

                return null;
        }

        $this->scanner->next();

        switch ($this->scanner->getTokenType()) {
            case QueryLexer::T_RANGE:
                $this->scanner->next();

                $value = new Node\Range($value->getToken(), $this->scanner->getToken());

                $this->scanner->next();

                break;
        }

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
     * @return \Gdbots\QueryParser\Node\AbstractQueryItem|null
     */
    protected function readExpression($tokenType)
    {
        switch ($tokenType) {
            case QueryLexer::T_OPEN_PARENTHESIS:
                return $this->readSubQuery();

            case QueryLexer::T_PHRASE:
                $text = new Node\Phrase($this->scanner->getToken());
                return $this->readTerm($this->scanner->next(), $text);

            case QueryLexer::T_WORD:
            case QueryLexer::T_DATE:
            case QueryLexer::T_NUMBER:
            case QueryLexer::T_URL:
                $word = new Node\Word($this->scanner->getToken(), $this->scanner->getTokenType());
                return $this->readTerm($this->scanner->next(), $word);

            case QueryLexer::T_HASHTAG:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    if ($expression->getTokenType() == QueryLexer::T_BOOST) {
                        $hashtag = new Node\Hashtag($expression->getNominator()->getToken());
                        $hashtag->setBoostBy($expression->getTerm()->getToken());
                        return $hashtag;
                    }

                    return new Node\Hashtag($expression->getToken());
                }

                $this->addError('Error: HASHTAG not followed by a valid expression.');

                break;

            case QueryLexer::T_MENTION:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    if ($expression->getTokenType() == QueryLexer::T_BOOST) {
                        $mention = new Node\Mention($expression->getNominator()->getToken());
                        $mention->setBoostBy($expression->getTerm()->getToken());
                        return $mention;
                    }

                    return new Node\Mention($expression->getToken());
                }

                $this->addError('Error: MENTION not followed by a valid expression.');

                break;

            case QueryLexer::T_EXCLUDE:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    $expression->setExcluded(true);
                    return $expression;
                }

                $this->addError('Error: EXCLUDE not followed by a valid expression.');

                break;

            case QueryLexer::T_INCLUDE:
                $expression = $this->readExpression($this->scanner->next());
                if ($expression) {
                    $expression->setIncluded(true);
                    return $expression;
                }

                $this->addError('Error: INCLUDE not followed by a valid expression.');

                break;

            case QueryLexer::T_ILLEGAL:
                $this->addError(sprintf(
                    'Error: Expected Expression. Found illegal character: "%s"',
                    $this->scanner->getTokenTypeText()
                ));

                break;

            case QueryLexer::T_QUOTE:
                $this->addError(sprintf(
                    'Error: Opening quote at pos %s lacks closing quote: "%s"',
                    $this->scanner->getPosition(),
                    $this->scanner->getToken()
                ));

                break;

            case QueryLexer::T_OR_OPERATOR:
                $this->addError(sprintf(
                    'Error: Expected Expression. OR operator found at pos %s "%s" remaining: "%s"',
                    $this->scanner->getPosition(),
                    $this->scanner->getToken(),
                    $this->scanner->getRemainingData()
                ));

                break;

            case QueryLexer::T_AND_OPERATOR:
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
     * @return \Gdbots\QueryParser\Node\AbstractQueryItem|null
     */
    protected function readOrExpressionList()
    {
        $expressions = [];

        do {
            $lastExpression = $this->readExpression($this->scanner->getTokenType());

            if ($this->scanner->getTokenType() == QueryLexer::T_BOOST) {
                /** @var \Gdbots\QueryParser\Node\AbstractQueryItem|null $expression */
                if ($expression = $this->readExpression($this->scanner->next())) {
                    $lastExpression->setBoostBy($expression->getToken());
                }
            }

            if ($this->scanner->getTokenType() == QueryLexer::T_RANGE) {
                /** @var \Gdbots\QueryParser\Node\AbstractQueryItem|null $expression */
                if ($expression = $this->readExpression($this->scanner->next())) {
                    $lastExpression->setToken(sprintf('%s..%s', $lastExpression->getToken(), $expression->getToken()));
                }
            }

            $expressions[] = $lastExpression;

        } while ($lastExpression && $this->scanner->getTokenType() == QueryLexer::T_OR_OPERATOR && $this->scanner->next());

        if (count($expressions) === 1) {
            return $expressions[0];
        }

        return new Node\OrExpressionList($expressions);
    }

    /**
     * Makes the parser read a list of statements. This can be:
     * - Expression
     * - Expression AND Expression AND ...
     *
     * @return \Gdbots\QueryParser\Node\AbstractQueryItem|null
     */
    protected function readAndExpressionList()
    {
        $this->scanner->next();

        $expressions = [];

        do {
            $lastExpression = $this->readOrExpressionList();
            $expressions[] = $lastExpression;

        } while ($lastExpression && $this->scanner->getTokenType() == QueryLexer::T_AND_OPERATOR && $this->scanner->next());

        switch ($this->scanner->getTokenType()) {
            case QueryLexer::T_CLOSE_PARENTHESIS:
            case QueryLexer::T_EOI:
                if (count($expressions) === 1) {
                    return $expressions[0];
                }

                return new Node\AndExpressionList($expressions);
        }

        $this->addError(sprintf('Error: Expected Expression. Found: "%s"', $this->scanner->getTokenTypeText()));

        return null;
    }

    /**
     * Makes the parser read expressions between parentheses.
     *
     * @return \Gdbots\QueryParser\Node\AbstractQueryItem|null
     */
    protected function readSubQuery()
    {
        $expressionlist = $this->readAndExpressionList();
        if ($this->scanner->getTokenType() == QueryLexer::T_CLOSE_PARENTHESIS) {
            $this->scanner->next();

            if (!($expressionlist instanceof Node\AbstractExpressionList)) {
                return $expressionlist;
            }

            return new Node\SubExpression($expressionlist);
        }

        $this->addError('Error: Expected `)` but end of stream reached.');

        return null;
    }
}
