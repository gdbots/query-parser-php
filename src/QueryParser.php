<?php
declare(strict_types=1);

namespace Gdbots\QueryParser;

use Gdbots\QueryParser\Enum\BoolOperator;
use Gdbots\QueryParser\Enum\ComparisonOperator;
use Gdbots\QueryParser\Node\Date;
use Gdbots\QueryParser\Node\DateRange;
use Gdbots\QueryParser\Node\Emoji;
use Gdbots\QueryParser\Node\Emoticon;
use Gdbots\QueryParser\Node\Field;
use Gdbots\QueryParser\Node\Hashtag;
use Gdbots\QueryParser\Node\Mention;
use Gdbots\QueryParser\Node\Node;
use Gdbots\QueryParser\Node\NumberRange;
use Gdbots\QueryParser\Node\Numbr;
use Gdbots\QueryParser\Node\Phrase;
use Gdbots\QueryParser\Node\Subquery;
use Gdbots\QueryParser\Node\Url;
use Gdbots\QueryParser\Node\Word;
use Gdbots\QueryParser\Node\WordRange;

/**
 * Parses a query and returns a ParsedQuery object with a set of
 * nodes per type, i.e. words, phrases, hashtags, etc.
 */
final class QueryParser
{
    private Tokenizer $tokenizer;
    private TokenStream $stream;

    /**
     * Constructs a new QueryParser.
     */
    public function __construct()
    {
        $this->tokenizer = new Tokenizer();
    }

    public function parse(string $input): ParsedQuery
    {
        $this->stream = $this->tokenizer->scan($input);
        $query = new ParsedQuery();

        while ($this->stream->next()) {
            $boolOperator = $this->getBoolOperator();
            $token = $this->stream->getCurrent();
            if ($token->typeEquals(Token::T_EOI)) {
                break;
            }

            $query->addNodes($this->createNodes($token, $boolOperator));
        }

        return $query;
    }

    /**
     * @param Token              $token
     * @param BoolOperator       $boolOperator
     * @param ComparisonOperator $comparisonOperator
     *
     * @return Node[]
     */
    private function createNodes(
        Token $token,
        BoolOperator $boolOperator,
        ?ComparisonOperator $comparisonOperator = null
    ): array {
        switch ($token->getType()) {
            case Token::T_WORD:
                $nodes = $this->createWord($token->getValue(), $boolOperator);
                break;

            case Token::T_DATE:
                $nodes = $this->createDate($token->getValue(), $boolOperator, $comparisonOperator);
                break;

            case Token::T_EMOJI:
                $nodes = $this->createEmoji($token->getValue(), $boolOperator);
                break;

            case Token::T_EMOTICON:
                $nodes = $this->createEmoticon($token->getValue(), $boolOperator);
                break;

            case Token::T_FIELD_START:
                $nodes = $this->handleField($token->getValue(), $boolOperator);
                break;

            case Token::T_HASHTAG:
                $nodes = $this->createHashtag($token->getValue(), $boolOperator);
                break;

            case Token::T_MENTION:
                $nodes = $this->createMention($token->getValue(), $boolOperator);
                break;

            case Token::T_NUMBER:
                $nodes = $this->createNumber($token->getValue(), $comparisonOperator);
                break;

            case Token::T_PHRASE:
                $nodes = $this->createPhrase($token->getValue(), $boolOperator);
                break;

            case Token::T_SUBQUERY_START:
                $nodes = $this->handleSubquery($boolOperator);
                break;

            case Token::T_URL:
                $nodes = $this->createUrl($token->getValue(), $boolOperator);
                break;

            default:
                $nodes = [];
                break;
        }

        return $nodes instanceof Node ? [$nodes] : $nodes;
    }

    /**
     * @param string       $fieldName
     * @param BoolOperator $boolOperator
     *
     * @return Field|Node[]|Node
     */
    private function handleField(string $fieldName, BoolOperator $boolOperator)
    {
        $lookahead = $this->stream->getLookahead();
        if (!$lookahead instanceof Token) {
            return $this->createWord($fieldName, $boolOperator);
        }

        $this->stream->next();

        switch ($lookahead->getType()) {
            case Token::T_RANGE_INCL_START:
            case Token::T_RANGE_EXCL_START:
                return $this->handleFieldWithRange($fieldName, $boolOperator);

            case Token::T_SUBQUERY_START:
                return $this->handleFieldWithSubquery($fieldName, $boolOperator);

            case Token::T_FIELD_END:
                return $this->createWord($fieldName, $boolOperator);

            default:
                break;
        }

        $this->stream->nextIfAnyOf([
            Token::T_REQUIRED,
            Token::T_PROHIBITED,
            Token::T_WILDCARD,
            Token::T_FUZZY,
            Token::T_BOOST,
        ]);

        $comparisonOperator = $this->getComparisonOperator();
        $fieldValue = $this->stream->getCurrent();
        $nodes = $this->createNodes($fieldValue, BoolOperator::OPTIONAL, $comparisonOperator);
        $this->stream->skipUntil(Token::T_FIELD_END);

        if (empty($nodes)) {
            return $this->createWord($fieldName, $boolOperator);
        }

        if (count($nodes) > 1) {
            return $nodes;
        }

        $m = $this->getModifiers();
        return new Field($fieldName, $nodes[0], $boolOperator, $m['use_boost'], $m['boost']);
    }

    /**
     * @param string       $fieldName
     * @param BoolOperator $boolOperator
     *
     * @return Field|Node[]|Node
     */
    private function handleFieldWithRange(string $fieldName, BoolOperator $boolOperator)
    {
        $exclusive = $this->stream->typeIs(Token::T_RANGE_EXCL_START);
        $matchTypes = true;
        $this->stream->next();

        switch ($this->stream->getCurrent()->getType()) {
            case Token::T_NUMBER:
                $lowerNode = $this->createNumber($this->stream->getCurrent()->getValue());
                break;

            case Token::T_DATE:
                $lowerNode = $this->createDate($this->stream->getCurrent()->getValue(), BoolOperator::OPTIONAL);
                break;

            case Token::T_WORD:
                $lowerNode = $this->createWord($this->stream->getCurrent()->getValue(), BoolOperator::OPTIONAL);
                break;

            default:
                $lowerNode = null;
                $matchTypes = false;
                break;
        }

        $this->stream->skipUntil(Token::T_TO);
        $this->stream->nextIf(Token::T_TO);

        switch ($this->stream->getCurrent()->getType()) {
            case Token::T_NUMBER:
                $upperNode = $this->createNumber($this->stream->getCurrent()->getValue());
                break;

            case Token::T_DATE:
                $upperNode = $this->createDate($this->stream->getCurrent()->getValue(), BoolOperator::OPTIONAL);
                break;

            case Token::T_WORD:
                $upperNode = $this->createWord($this->stream->getCurrent()->getValue(), BoolOperator::OPTIONAL);
                break;

            default:
                $upperNode = null;
                $matchTypes = false;
                break;
        }

        $this->stream->skipUntil(Token::T_FIELD_END);

        // todo: add field name and/or nodes that aren't null as words?
        // todo: handle mismatched node
        if ($matchTypes && !$lowerNode instanceof $upperNode) {
            $nodes = [];

            if ($lowerNode instanceof Node) {
                $nodes[] = $lowerNode;
            }

            if ($upperNode instanceof Node) {
                $nodes[] = $upperNode;
            }

            if (empty($nodes)) {
                return $this->createWord($fieldName, $boolOperator);
            }

            $m = $this->getModifiers();

            if (count($nodes) === 1) {
                return new Field($fieldName, $nodes[0], $boolOperator, $m['use_boost'], $m['boost']);
            }

            $subquery = new Subquery($nodes, null, $m['use_boost'], $m['boost']);
            return new Field($fieldName, $subquery, $boolOperator, $m['use_boost'], $m['boost']);
        }

        $m = $this->getModifiers();

        if ($lowerNode instanceof Numbr || $upperNode instanceof Numbr) {
            $range = new NumberRange($lowerNode, $upperNode, $exclusive);
            return new Field($fieldName, $range, $boolOperator, $m['use_boost'], $m['boost']);
        } elseif ($lowerNode instanceof Date || $upperNode instanceof Date) {
            $range = new DateRange($lowerNode, $upperNode, $exclusive);
            return new Field($fieldName, $range, $boolOperator, $m['use_boost'], $m['boost']);
        } elseif ($lowerNode instanceof Word || $upperNode instanceof Word) {
            $range = new WordRange($lowerNode, $upperNode, $exclusive);
            return new Field($fieldName, $range, $boolOperator, $m['use_boost'], $m['boost']);
        }

        return $this->createWord($fieldName, $boolOperator);
    }

    /**
     * @param string       $fieldName
     * @param BoolOperator $boolOperator
     *
     * @return Field|Node
     */
    private function handleFieldWithSubquery(string $fieldName, BoolOperator $boolOperator): Node
    {
        $this->stream->nextIf(Token::T_SUBQUERY_START);
        $subquery = $this->handleSubquery($boolOperator);
        $this->stream->skipUntil(Token::T_FIELD_END);

        if ($subquery instanceof Subquery) {
            $m = $this->getModifiers();
            return new Field($fieldName, $subquery, $boolOperator, $m['use_boost'], $m['boost']);
        }

        if (empty($subquery)) {
            return $this->createWord($fieldName, $boolOperator);
        }

        $m = $this->getModifiers();
        return new Field($fieldName, $subquery, $boolOperator, $m['use_boost'], $m['boost']);
    }

    /**
     * @param BoolOperator $queryBoolOperator
     *
     * @return Subquery|Node[]|Node
     */
    private function handleSubquery(BoolOperator $queryBoolOperator)
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

        if (empty($nodes)) {
            return [];
        }

        $m = $this->getModifiers();

        /*
         * if we only found one node within the subquery then we'll take the original query bool
         * operator, recreate the node with that (unless it has its own) and any modifiers found
         * and magically convert "+(cats)^5 to "+cats^5" or "-(+cats)~2 to "+cats~2" etc.
         */
        if (count($nodes) === 1) {
            $data = $nodes[0]->toArray();

            if (!isset($data['bool_operator'])) {
                $data['bool_operator'] = $queryBoolOperator->value;
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

        return new Subquery($nodes, $queryBoolOperator, $m['use_boost'], $m['boost']);
    }

    private function createDate(
        string $value,
        BoolOperator $boolOperator,
        ?ComparisonOperator $comparisonOperator = null
    ): Date {
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

    private function createEmoji(string $value, BoolOperator $boolOperator): Emoji
    {
        $boolOperator = $boolOperator === BoolOperator::OPTIONAL ? BoolOperator::REQUIRED : $boolOperator;
        $m = $this->getModifiers();
        return new Emoji($value, $boolOperator, $m['use_boost'], $m['boost']);
    }

    private function createEmoticon(string $value, BoolOperator $boolOperator): Emoticon
    {
        $boolOperator = $boolOperator === BoolOperator::OPTIONAL ? BoolOperator::REQUIRED : $boolOperator;
        $m = $this->getModifiers();
        return new Emoticon($value, $boolOperator, $m['use_boost'], $m['boost']);
    }

    private function createHashtag(string $value, BoolOperator $boolOperator): Hashtag
    {
        $boolOperator = $boolOperator === BoolOperator::OPTIONAL ? BoolOperator::REQUIRED : $boolOperator;
        $m = $this->getModifiers();
        return new Hashtag($value, $boolOperator, $m['use_boost'], $m['boost']);
    }

    private function createMention(string $value, BoolOperator $boolOperator): Mention
    {
        $boolOperator = $boolOperator === BoolOperator::OPTIONAL ? BoolOperator::REQUIRED : $boolOperator;
        $m = $this->getModifiers();
        return new Mention($value, $boolOperator, $m['use_boost'], $m['boost']);
    }

    private function createNumber(float $value, ?ComparisonOperator $comparisonOperator = null): Numbr
    {
        // move the stream and ignore them if they exist
        $this->getModifiers();
        return new Numbr($value, $comparisonOperator);
    }

    private function createPhrase(string $value, BoolOperator $boolOperator): Phrase
    {
        $m = $this->getModifiers();
        return new Phrase($value, $boolOperator, $m['use_boost'], $m['boost'], $m['use_fuzzy'], $m['fuzzy']);
    }

    private function createUrl(string $value, BoolOperator $boolOperator): Url
    {
        $m = $this->getModifiers();
        return new Url($value, $boolOperator, $m['use_boost'], $m['boost']);
    }

    private function createWord(string $value, BoolOperator $boolOperator): Word
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

    private function getBoolOperator(): BoolOperator
    {
        if ($this->stream->nextIf(Token::T_REQUIRED)
            || $this->stream->lookaheadTypeIs(Token::T_AND)
            || $this->stream->prevTypeIs(Token::T_AND)
        ) {
            return BoolOperator::REQUIRED;
        }

        if ($this->stream->nextIf(Token::T_PROHIBITED)) {
            return BoolOperator::PROHIBITED;
        }

        return BoolOperator::OPTIONAL;
    }

    private function getComparisonOperator(): ?ComparisonOperator
    {
        if ($this->stream->nextIf(Token::T_GREATER_THAN)) {
            $op = ComparisonOperator::GT->value;
        } elseif ($this->stream->nextIf(Token::T_LESS_THAN)) {
            $op = ComparisonOperator::LT->value;
        } else {
            return null;
        }

        if ($this->stream->nextIf(Token::T_EQUALS)) {
            $op .= 'e';
        }

        return ComparisonOperator::from($op);
    }

    private function getModifiers(): array
    {
        $array = [
            'trailing_wildcard' => $this->stream->nextIfLookahead(Token::T_WILDCARD),
            'use_boost'         => false,
            'boost'             => Node::DEFAULT_BOOST,
            'use_fuzzy'         => false,
            'fuzzy'             => Node::DEFAULT_FUZZY,
        ];

        if ($this->stream->nextIfLookahead(Token::T_BOOST) && $this->stream->nextIfLookahead(Token::T_NUMBER)) {
            $array['use_boost'] = true;
            $array['boost'] = (float)$this->stream->getCurrent()->getValue();
        }

        if ($this->stream->nextIfLookahead(Token::T_FUZZY)) {
            $array['use_fuzzy'] = true;
            if ($this->stream->nextIfLookahead(Token::T_NUMBER)) {
                $array['fuzzy'] = (int)$this->stream->getCurrent()->getValue();
            }
        }

        return $array;
    }
}
