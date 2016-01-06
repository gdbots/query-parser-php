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

            //echo str_pad($token->getTypeName(), 19, ' ') . ($token->getValue() ? ' => ' . $token->getValue() : '') . PHP_EOL;

            if ($token->typeEquals(Token::T_EOI)) {
                break;
            }

            $this->query->addNodes($this->createNodes($token, $boolOperator));
        }

        return $this->query;
    }

    /**
     * @param Token $token
     * @param BoolOperator $boolOperator
     * @param ComparisonOperator $comparisonOperator
     *
     * @return Node[]
     */
    protected function createNodes(
        Token $token,
        BoolOperator $boolOperator,
        ComparisonOperator $comparisonOperator = null
    ) {
        switch ($token->getType()) {
            case Token::T_WORD:
                $nodes = $this->handleWord($token->getValue(), $boolOperator);
                break;

            case Token::T_DATE:
                $nodes = $this->handleDate($token->getValue(), $boolOperator, $comparisonOperator);
                break;

            case Token::T_EMOJI:
                $nodes = $this->handleEmoji($token->getValue(), $boolOperator);
                break;

            case Token::T_EMOTICON:
                $nodes = $this->handleEmoticon($token->getValue(), $boolOperator);
                break;

            case Token::T_FILTER_START:
                $nodes = $this->handleFilter($token->getValue(), $boolOperator);
                break;

            case Token::T_HASHTAG:
                $nodes = $this->handleHashtag($token->getValue(), $boolOperator);
                break;

            case Token::T_MENTION:
                $nodes = $this->handleMention($token->getValue(), $boolOperator);
                break;

            case Token::T_NUMBER:
                $nodes = $this->handleNumber($token->getValue(), $comparisonOperator);
                break;

            case Token::T_PHRASE:
                $nodes = $this->handlePhrase($token->getValue(), $boolOperator);
                break;

            case Token::T_SUBQUERY_START:
                $nodes = $this->handleSubquery($boolOperator);
                break;

            case Token::T_URL:
                $nodes = $this->handleUrl($token->getValue(), $boolOperator);
                break;

            default:
                $nodes = [];
                break;
        }

        return $nodes instanceof Node ? [$nodes] : $nodes;
    }

    /**
     * @param string $value
     * @param BoolOperator $boolOperator
     * @param ComparisonOperator $comparisonOperator
     *
     * @return Date
     */
    protected function handleDate($value, BoolOperator $boolOperator, ComparisonOperator $comparisonOperator = null)
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
    protected function handleEmoji($value, BoolOperator $boolOperator)
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
    protected function handleEmoticon($value, BoolOperator $boolOperator)
    {
        $boolOperator = $boolOperator->equals(BoolOperator::OPTIONAL()) ? BoolOperator::REQUIRED() : $boolOperator;
        $m = $this->getModifiers();
        return new Emoticon($value, $boolOperator, $m['use_boost'], $m['boost']);
    }

    /**
     * @param BoolOperator $boolOperator
     *
     * @param string $fieldName
     * @return Node[]|Node
     */
    protected function handleFilter($fieldName, BoolOperator $boolOperator)
    {
        $lookahead = $this->stream->getLookahead();
        if (!$lookahead instanceof Token) {
            return $this->handleWord($fieldName, $boolOperator);
        }

        switch ($lookahead->getType()) {
            case Token::T_RANGE_INCL_START:
            case Token::T_RANGE_EXCL_START:
                return $this->handleFilterWithRange($boolOperator);

            case Token::T_FILTER_END:
                return $this->handleWord($fieldName, $boolOperator);

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
        $nodes = $this->createNodes($fieldValue, BoolOperator::OPTIONAL(), $comparisonOperator);

        // todo: handle no node created scenario
        // todo: handle multiple nodes created scenario?

        $this->stream->next();
        $m = $this->getModifiers();
        $filter = new Filter(
            $fieldName,
            $boolOperator,
            $m['use_boost'],
            $m['boost'],
            $nodes[0]
        );

        return $filter;
    }

    /**
     * @param BoolOperator $boolOperator
     *
     * @return Filter|Node[]|Node
     */
    protected function handleFilterWithRange(BoolOperator $boolOperator)
    {
        $fieldName = $this->stream->getCurrent()->getValue();
        $this->stream->next();

        $exclusive = $this->stream->typeIs(Token::T_RANGE_EXCL_START);
        $matchTypes = true;
        $this->stream->next();

        switch ($this->stream->getCurrent()->getType()) {
            case Token::T_NUMBER:
                $lowerNode = $this->handleNumber($this->stream->getCurrent()->getValue());
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
                $upperNode = $this->handleNumber($this->stream->getCurrent()->getValue());
                $this->stream->skipUntil(Token::T_FILTER_END);
                break;

            default:
                $upperNode = null;
                $matchTypes = false;
                break;
        }

        if ($matchTypes && !$lowerNode instanceof $upperNode) {
            // todo: add field name and/or nodes that aren't null as words?
            return [];
        }

        $this->stream->skipUntil(Token::T_FILTER_END);
        $m = $this->getModifiers();

        if ($lowerNode instanceof Number) {
            $range = new NumberRange($lowerNode, $upperNode, $exclusive);
            return new Filter($fieldName, $boolOperator, $m['use_boost'], $m['boost'], null, $range);
        }

        return [];
    }

    /**
     * @param string $value
     * @param BoolOperator $boolOperator
     *
     * @return Hashtag
     */
    protected function handleHashtag($value, BoolOperator $boolOperator)
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
    protected function handleMention($value, BoolOperator $boolOperator)
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
    protected function handleNumber($value, ComparisonOperator $comparisonOperator = null)
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
    protected function handlePhrase($value, BoolOperator $boolOperator)
    {
        $m = $this->getModifiers();
        return new Phrase($value, $boolOperator, $m['use_boost'], $m['boost'], $m['use_fuzzy'], $m['fuzzy']);
    }

    /**
     * @param BoolOperator $queryBoolOperator
     *
     * @return Subquery|Node[]|Node
     */
    protected function handleSubquery(BoolOperator $queryBoolOperator)
    {
        $this->stream->nextIf(Token::T_SUBQUERY_START);
        /** @var Node[] $nodes */
        $nodes = [];

        do {
            $boolOperator = $this->getBoolOperator();
            $comparisonOperator = $this->getComparisonOperator();
            $nodes = array_merge(
                $nodes,
                $this->createNodes($this->stream->getCurrent(), $boolOperator, $comparisonOperator)
            );

            if (!$this->stream->next()) {
                break;
            }
        } while (!$this->stream->typeIs(Token::T_SUBQUERY_END));

        // move the stream and ignore them if there are no nodes for the subquery
        $m = $this->getModifiers();

        if (empty($nodes)) {
            return [];
        }

        /*
         * if we only found one node within the subquery then we'll take the original query bool
         * operator, recreate the node with that (unless it has its own) and any modifiers found
         * and magically convert "+(cats)^5 to "+cats^5" or "-(+cats)~2 to "+cats~2" etc.
         */
        if (count($nodes) === 1) {
            $data = $nodes[0]->toArray();

            if (!isset($data['bool_operator'])) {
                $data['bool_operator'] = $queryBoolOperator;
            }

            if (!isset($data['use_boost'])) {
                $data['use_boost'] = $m['use_boost'];
            }

            if (!isset($data['boost'])) {
                $data['boost'] = $m['boost'];
            }

            if (!isset($data['use_fuzzy'])) {
                $data['use_fuzzy'] = $m['use_fuzzy'];
            }

            if (!isset($data['fuzzy'])) {
                $data['fuzzy'] = $m['fuzzy'];
            }

            if (!isset($data['trailing_wildcard'])) {
                $data['trailing_wildcard'] = $m['trailing_wildcard'];
            }

            return $nodes[0]::fromArray($data);
        }

        return new Subquery($nodes, $m['use_boost'], $m['boost']);
    }

    /**
     * @param string $value
     * @param BoolOperator $boolOperator
     *
     * @return Url
     */
    protected function handleUrl($value, BoolOperator $boolOperator)
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
    protected function handleWord($value, BoolOperator $boolOperator)
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
