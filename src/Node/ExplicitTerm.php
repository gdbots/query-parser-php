<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Parser\QueryScanner;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

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
            $this->nominator->getExpression()->addParentTokenType($tokenType, $this->term->getToken());
        } elseif ($this->nominator instanceof QueryItem) {
            $this->nominator->addParentTokenType($tokenType, $this->term->getToken());
        }
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

        if ($tokenType) {
            if ($tokenType == $this->getTokenType()) {
                if (in_array($this->getTokenType(), [QueryScanner::T_BOOST])) {
                    if ($this->getNominator() && $this->getNominator() instanceof QueryItem) {
                        $items = array_merge_recursive($items, $this->getNominator()->getQueryItemsByTokenType($this->getNominator()->getTokenType()));
                    }
                } else {
                    $items[] = $this;
                }
            } elseif (in_array($this->getTokenType(), [QueryScanner::T_BOOST])) {
                if ($this->getNominator() && $this->getNominator() instanceof QueryItem) {
                    $items = array_merge_recursive($items, $this->getNominator()->getQueryItemsByTokenType($tokenType));
                }
            }
        } else {
            if (in_array($this->getTokenType(), [QueryScanner::T_BOOST])) {
                if ($this->getNominator() && $this->getNominator() instanceof QueryItem) {
                    $items = array_merge_recursive($items, $this->getNominator()->getQueryItemsByTokenType());
                }
            } else {
                $items[QueryScanner::$typeStrings[$this->getTokenType()]][] = $this;
            }
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorInterface $visitor)
    {
        $visitor->visitExplicitTerm($this);
    }
}
