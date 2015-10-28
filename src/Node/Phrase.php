<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\QueryLexer;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class Phrase extends AbstractSimpleTerm
{
    /**
     * @param string $text
     */
    public function __construct($text)
    {
        parent::__construct(QueryLexer::T_PHRASE, $this->stripQuotes($text));
    }

    /**
     * @return string
     */
    public function getQuotedText()
    {
        return sprintf('"%s"', $this->getToken());
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'Expression' => 'Phrase',
            'Term' => $this->getToken()
        ];
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function stripQuotes($text)
    {
        return strlen($text) > 2
            ? substr($text, 1, strlen($text)-2)
            : $text;
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorInterface $visitor)
    {
        return $visitor->visitPhrase($this);
    }
}
