<?php

namespace Gdbots\QueryParser\Node;

use Gdbots\QueryParser\Parser\QueryScanner;
use Gdbots\QueryParser\Visitor\QueryItemVisitorinterface;

class Text extends SimpleTerm
{
    /**
     * @param string $text
     */
    public function __construct($text)
    {
        parent::__construct(QueryScanner::T_TEXT, $this->stripQuotes($text));
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
            'Expression' => 'Text',
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
        } else {
            return $text;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function accept(QueryItemVisitorinterface $visitor)
    {
        $visitor->visitText($this);
    }
}
