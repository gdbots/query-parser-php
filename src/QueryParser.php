<?php

namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Enum\BoolOperator;
use Gdbots\QueryParser\Enum\ComparisonOperator;
use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Filter;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\Node;
use Gdbots\QueryParser\Node\Number;
use Gdbots\QueryParser\Node\NumberRange;
use Gdbots\QueryParser\Node\Phrase;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Url;
use Gdbots\QueryParser\Node\Word;

/**
 * Parses a query and returns a ParsedQuery object with a set of
 * nodes per type, i.e. words, phrases, hashtags, etc.
 * The subquery aspects that are tokenized are ignored by this parser.
 */
class QueryParser
{
    /** @var Tokenizer */
    protected $tokenizer;

    /** @var TokenStream */
    protected $stream;

    /** @var ParsedQuery */
    protected $query;

    /**
     * Constructs a new SimpleParser.
     */
    public function __construct()
    {
        $this->tokenizer = new Tokenizer();
    }

    /**
     * @param string $input
     * @return ParsedQuery
     */
    public function parse($input)
    {
        $this->stream = $this->tokenizer->scan($input);
        $this->query = new ParsedQuery();

        while ($this->stream->next()) {
            $boolOperator = $this->getBoolOperator();
            $token = $this->stream->getCurrent();

            echo str_pad($token->getTypeName(), 19, ' ') . ($token->getValue() ? ' => ' . $token->getValue() : '') . PHP_EOL;

            if ($token->typeEquals(Token::T_EOI)) {
                break;
            }

            $node = $this->createNode($token, $boolOperator);
            if (null !== $node) {
                $this->query->addNode($node);
            }

        }

        return $this->query;
    }

    /**
     * @param Token $token
     * @param BoolOperator $boolOperator
     * @param ComparisonOperator $comparisonOperator
     *
     * @return Node|null
     */
    protected function createNode(
        Token $token,
        BoolOperator $boolOperator,
        ComparisonOperator $comparisonOperator = null
    ) {
        $node = null;

        switch ($token->getType()) {
            case Token::T_WORD:
                $node = $this->createWord($token->getValue(), $boolOperator);
                break;

            case Token::T_DATE:
                $node = $this->createDate($token->getValue(), $boolOperator, $comparisonOperator);
                break;

            case Token::T_EMOJI:
                $node = $this->createEmoji($token->getValue(), $boolOperator);
                break;

            case Token::T_EMOTICON:
                $node = $this->createEmoticon($token->getValue(), $boolOperator);
                break;

            case Token::T_FILTER_START:
                $node = $this->createFilter($boolOperator);
                break;

            case Token::T_HASHTAG:
                $node = $this->createHashtag($token->getValue(), $boolOperator);
                break;

            case Token::T_MENTION:
                $node = $this->createMention($token->getValue(), $boolOperator);
                break;

            case Token::T_NUMBER:
                $node = $this->createNumber($token->getValue(), $comparisonOperator);
                break;

            case Token::T_PHRASE:
                $node = $this->createPhrase($token->getValue(), $boolOperator);
                break;

            case Token::T_SUBQUERY_START:
                $node = $this->createSubquery();
                break;

            case Token::T_URL:
                $node = $this->createUrl($token->getValue(), $boolOperator);
                break;

            default:
                break;
        }

        return $node;
    }

    /**
     * @param string $value
     * @param BoolOperator $boolOperator
     * @param ComparisonOperator $comparisonOperator
     *
     * @return Date
     */
    protected function createDate($value, BoolOperator $boolOperator, ComparisonOperator $comparisonOperator = null)
    {
        $m = $this->getModifiers();
        return new Date(
            $value,
            $boolOperator,
            $m['use_boost'],
            $m['boost'],
            $m['use_fuzzy'],
            $m['fuzzy'],
            $comparisonOperator
        );
    }

    /**
     * @param string $value
     * @param BoolOperator $boolOperator
     *
     * @return Emoji
     */
    protected function createEmoji($value, BoolOperator $boolOperator)
    {
        $boolOperator = $boolOperator->equals(BoolOperator::OPTIONAL()) ? BoolOperator::REQUIRED() : $boolOperator;
        $m = $this->getModifiers();
        return new Emoji($value, $boolOperator, $m['use_boost'], $m['boost']);
    }

    /**
     * @param string $value
     * @param BoolOperator $boolOperator
     *
     * @return Emoticon
     */
    protected function createEmoticon($value, BoolOperator $boolOperator)
    {
        $boolOperator = $boolOperator->equals(BoolOperator::OPTIONAL()) ? BoolOperator::REQUIRED() : $boolOperator;
        $m = $this->getModifiers();
        return new Emoticon($value, $boolOperator, $m['use_boost'], $m['boost']);
    }

    /**
     * @param BoolOperator $boolOperator
     *
     * @return Filter|null
     */
    protected function createFilter(BoolOperator $boolOperator)
    {
        $fieldName = $this->stream->getCurrent()->getValue();

        $lookahead = $this->stream->getLookahead();
        if (!$lookahead instanceof Token) {
            return $this->createWord($fieldName, $boolOperator);
        }

        switch ($lookahead->getType()) {
            case Token::T_RANGE_INCL_START:
            case Token::T_RANGE_EXCL_START:
                return $this->createFilterWithRange($boolOperator);

            default:
                break;
        }

        $this->stream->next();
        $this->stream->nextIfAnyOf([
            Token::T_REQUIRED,
            Token::T_PROHIBITED,
            Token::T_WILDCARD,
            Token::T_FUZZY,
            Token::T_BOOST,
        ]);

        $comparisonOperator = $this->getComparisonOperator();
        $fieldValue = $this->stream->getCurrent();
        $node = $this->createNode($fieldValue, BoolOperator::OPTIONAL(), $comparisonOperator);

        // todo: handle no node created scenario

        $this->stream->next();
        $m = $this->getModifiers();
        $filter = new Filter(
            $fieldName,
            $boolOperator,
            $m['use_boost'],
            $m['boost'],
            $node
        );

        return $filter;
    }

    /**
     * @param BoolOperator $boolOperator
     *
     * @return Filter|null
     */
    protected function createFilterWithRange(BoolOperator $boolOperator)
    {
        $fieldName = $this->stream->getCurrent()->getValue();
        $this->stream->next();

        $exclusive = $this->stream->typeIs(Token::T_RANGE_EXCL_START);
        $matchTypes = true;
        $this->stream->next();

        switch ($this->stream->getCurrent()->getType()) {
            case Token::T_NUMBER:
                $lowerNode = $this->createNumber($this->stream->getCurrent()->getValue());
                $this->stream->skipUntil(Token::T_TO);
                break;

            default:
                $lowerNode = null;
                $matchTypes = false;
                break;
        }

        $this->stream->next();

        switch ($this->stream->getCurrent()->getType()) {
            case Token::T_NUMBER:
                $upperNode = $this->createNumber($this->stream->getCurrent()->getValue());
                $this->stream->skipUntil(Token::T_FILTER_END);
                break;

            default:
                $upperNode = null;
                $matchTypes = false;
                break;
        }

        if ($matchTypes && !$lowerNode instanceof $upperNode) {
            // todo: add field name and/or nodes that aren't null as words?
            return null;
        }

        $this->stream->skipUntil(Token::T_FILTER_END);
        $m = $this->getModifiers();

        if ($lowerNode instanceof Number) {
            $range = new NumberRange($lowerNode, $upperNode, $exclusive);
            return new Filter($fieldName, $boolOperator, $m['use_boost'], $m['boost'], null, $range);
        }
    }

    /**
     * @param string $value
     * @param BoolOperator $boolOperator
     *
     * @return Hashtag
     */
    protected function createHashtag($value, BoolOperator $boolOperator)
    {
        $boolOperator = $boolOperator->equals(BoolOperator::OPTIONAL()) ? BoolOperator::REQUIRED() : $boolOperator;
        $m = $this->getModifiers();
        return new Hashtag($value, $boolOperator, $m['use_boost'], $m['boost']);
    }

    /**
     * @param string $value
     * @param BoolOperator $boolOperator
     *
     * @return Mention
     */
    protected function createMention($value, BoolOperator $boolOperator)
    {
        $boolOperator = $boolOperator->equals(BoolOperator::OPTIONAL()) ? BoolOperator::REQUIRED() : $boolOperator;
        $m = $this->getModifiers();
        return new Mention($value, $boolOperator, $m['use_boost'], $m['boost']);
    }

    /**
     * @param float $value
     * @param ComparisonOperator $comparisonOperator
     *
     * @return Number
     */
    protected function createNumber($value, ComparisonOperator $comparisonOperator = null)
    {
        // move the stream and ignore them if they exist
        $this->getModifiers();
        return new Number($value, $comparisonOperator);
    }

    /**
     * @param string $value
     * @param BoolOperator $boolOperator
     *
     * @return Phrase
     */
    protected function createPhrase($value, BoolOperator $boolOperator)
    {
        $m = $this->getModifiers();
        return new Phrase($value, $boolOperator, $m['use_boost'], $m['boost'], $m['use_fuzzy'], $m['fuzzy']);
    }

    /**
     * @return Subquery|null
     */
    protected function createSubquery()
    {
        $this->stream->nextIf(Token::T_SUBQUERY_START);
        $nodes = [];

        do {
            $boolOperator = $this->getBoolOperator();
            $comparisonOperator = $this->getComparisonOperator();
            $node = $this->createNode($this->stream->getCurrent(), $boolOperator, $comparisonOperator);
            if ($node instanceof Node) {
                $nodes[] = $node;
            }

            if (!$this->stream->next()) {
                break;
            }
        } while (!$this->stream->typeIs(Token::T_SUBQUERY_END));

        // move the stream and ignore them if there are no nodes for the subquery
        $m = $this->getModifiers();

        if (empty($nodes)) {
            return null;
        }

        // todo: if only one node, return the node itself?

        return new Subquery($nodes, $m['use_boost'], $m['boost']);
    }

    /**
     * @param string $value
     * @param BoolOperator $boolOperator
     *
     * @return Url
     */
    protected function createUrl($value, BoolOperator $boolOperator)
    {
        $m = $this->getModifiers();
        return new Url($value, $boolOperator, $m['use_boost'], $m['boost']);
    }

    /**
     * @param string $value
     * @param BoolOperator $boolOperator
     *
     * @return Word
     */
    protected function createWord($value, BoolOperator $boolOperator)
    {
        $m = $this->getModifiers();
        return new Word(
            $value,
            $boolOperator,
            $m['use_boost'],
            $m['boost'],
            $m['use_fuzzy'],
            $m['fuzzy'],
            $m['trailing_wildcard']
        );
    }

    /**
     * @param int $default
     *
     * @return BoolOperator
     */
    protected function getBoolOperator($default = BoolOperator::OPTIONAL)
    {
        if ($this->stream->nextIf(Token::T_REQUIRED)
            || $this->stream->lookaheadTypeIs(Token::T_AND)
            || $this->stream->prevTypeIs(Token::T_AND)
        ) {
            return BoolOperator::REQUIRED();
        }

        if ($this->stream->nextIf(Token::T_PROHIBITED)) {
            return BoolOperator::PROHIBITED();
        }

        return BoolOperator::create($default);
    }

    /**
     * @return ComparisonOperator|null
     */
    protected function getComparisonOperator()
    {
        if ($this->stream->nextIf(Token::T_GREATER_THAN)) {
            $op = ComparisonOperator::GT;
        } elseif ($this->stream->nextIf(Token::T_LESS_THAN)) {
            $op = ComparisonOperator::LT;
        } else {
            return null;
        }

        if ($this->stream->nextIf(Token::T_EQUALS)) {
            $op .= 'e';
        }

        return ComparisonOperator::create($op);
    }

    /**
     * @return array
     */
    protected function getModifiers()
    {
        $array = [
            'trailing_wildcard' => $this->stream->nextIfLookahead(Token::T_WILDCARD),
            'use_boost' => false,
            'boost' => Node::DEFAULT_BOOST,
            'use_fuzzy' => false,
            'fuzzy' => Node::DEFAULT_FUZZY,
        ];

        if ($this->stream->nextIfLookahead(Token::T_BOOST) && $this->stream->nextIfLookahead(Token::T_NUMBER)) {
            $array['use_boost'] = true;
            $array['boost'] = $this->stream->getCurrent()->getValue();
        }

        if ($this->stream->nextIfLookahead(Token::T_FUZZY)) {
            $array['use_fuzzy'] = true;
            if ($this->stream->nextIfLookahead(Token::T_NUMBER)) {
                $array['fuzzy'] = $this->stream->getCurrent()->getValue();
            }
        }

        return $array;
    }
}
