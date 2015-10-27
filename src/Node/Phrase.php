<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Parser\QueryScanner;
use Gdbots\QueryParser\Visitor\QueryItemVisitorInterface;

class Phrase extends SimpleTerm
{
    /**
     * @param string $text
     */
    public function __construct($text)
    {
        parent::__construct(QueryScanner::T_PHRASE, $this->stripQuotes($text));
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
     * @return string
     */
    public function stripQuotes($text)
    {
        if (strlen($text) > 2) {
            return substr($text, 1, strlen($text)-2);
        }
        return $text;

    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorInterface $visitor)
    {
        return $visitor->visitPhrase($this);
    }
}
