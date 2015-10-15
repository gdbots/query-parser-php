<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Visitor\QueryItemVisitorinterface;

class ExplicitTerm extends QueryItem
{
    /**
     * @var string
     */
    protected $nominator;

    /**
     * @var string
     */
    protected $tokenType;

    /**
     * @var SimpleTerm
     */
    protected $term;

    /**
     * @param string     $nominator
     * @param string     $tokenType
     * @param SimpleTerm $term
     */
    public function __construct($nominator, $tokenType, SimpleTerm $term)
    {
        $this->nominator = $nominator;
        $this->tokenType = $tokenType;
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
            'Term' => $this->term
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorinterface $visitor)
    {
        $visitor->visitExplicitTerm($this);
    }
}
