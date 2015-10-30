<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class ExplicitTerm extends AbstractQueryItem
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
     * @var AbstractSimpleTerm
     */
    protected $term;

    /**
     * @param string|AbstractQueryItem $nominator
     * @param int                      $tokenType
     * @param string                   $tokenTypeText
     * @param AbstractSimpleTerm       $term
     */
    public function __construct($nominator, $tokenType, $tokenTypeText, AbstractSimpleTerm $term)
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
     * @return AbstractSimpleTerm
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

        if ($tokenType) {
            if ($tokenType == $this->getTokenType()) {
                $items[] = $this;
            }
        } else {
            $items[QueryLexer::$typeStrings[$this->getTokenType()]][] = $this;
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorInterface $visitor)
    {
        return $visitor->visitExplicitTerm($this);
    }
}
