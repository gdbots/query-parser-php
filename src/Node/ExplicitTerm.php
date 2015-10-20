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
     * @param string     $nominator
     * @param int        $tokenType
     * @param string     $tokenTypeText
     * @param SimpleTerm $term
     */
    public function __construct($nominator, $tokenType, $tokenTypeText, SimpleTerm $term)
    {
        $this->nominator = $nominator;
        $this->tokenType = $tokenType;
        $this->tokenTypeText = $tokenTypeText;
        $this->term = $term;
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
    public function getQueryItemsByTokenType($tokenType)
    {
        $items = [];
        $items = array_merge($items, $this->getNominator()->getQueryItemsByTokenType($tokenType));
        $items = array_merge($items, $this->getTerm()->getQueryItemsByTokenType($tokenType));

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
