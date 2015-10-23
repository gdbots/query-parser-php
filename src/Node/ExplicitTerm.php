<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Parser\QueryScanner;
use Gdbots\QueryParser\Visitor\QueryItemVisitorinterface;

class ExplicitTerm extends QueryItem
{
    /**
     * @var string
     */
    protected $nominator;

    /**
     * @var int
     */
    protected $tokenType;

    /**
     * @var string
     */
    protected $tokenTypeText;

    /**
     * @var SimpleTerm
     */
    protected $term;

    /**
     * @param string|QueryItem $nominator
     * @param int              $tokenType
     * @param string           $tokenTypeText
     * @param SimpleTerm       $term
     */
    public function __construct($nominator, $tokenType, $tokenTypeText, SimpleTerm $term)
    {
        $this->nominator = $nominator;
        $this->tokenType = $tokenType;
        $this->tokenTypeText = $tokenTypeText;
        $this->term = $term;

        if ($this->nominator instanceof CompositeExpression) {
            $this->nominator->getExpression()->addParentTokenType($tokenType);
        }
        if ($this->nominator instanceof QueryItem) {
            $this->nominator->addParentTokenType($tokenType);
        }

        $this->term->addParentTokenType($tokenType);
    }

    /**
     * @return string
     */
    public function getNominator()
    {
        return $this->nominator;
    }

    /**
     * @return int
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @return string
     */
    public function getTokenTypeText()
    {
        return $this->tokenTypeText ?: '-';
    }

    /**
     * @return SimpleTerm
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Expression' => 'Explicit Term',
            'Nominator' => $this->nominator,
            'TokenType' => $this->tokenType,
            'TokenTypeText' => $this->tokenTypeText,
            'Term' => $this->term
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryItemsByTokenType($tokenType = null)
    {
        $items = [];

        if ($tokenType === null) {
            $items[QueryScanner::$typeStrings[$this->getTokenType()]][] = $this;
        } elseif ($this->getTokenType() == $tokenType) {
            $items[] = $this;
        }

        if (!($this->getNominator() instanceof SimpleTerm)) {
            $items = array_merge_recursive($items, $this->getNominator()->getQueryItemsByTokenType($tokenType));
        }
        if (!($this->getTerm() instanceof SimpleTerm)) {
            $items = array_merge_recursive($items, $this->getTerm()->getQueryItemsByTokenType($tokenType));
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorinterface $visitor)
    {
        $visitor->visitExplicitTerm($this);
    }
}
