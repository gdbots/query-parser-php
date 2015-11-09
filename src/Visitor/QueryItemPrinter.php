<?php

namespace Gdbots\QueryParser\Visitor;

use Gdbots\QueryParser\Node;
use Gdbots\QueryParser\QueryLexer;

class QueryItemPrinter implements QueryItemVisitorInterface
{
    /**
     * @var integer depth
     */
    private $depth = 0;

    /**
     * @return string
     */
    private function indent()
    {
        return sprintf('%s ', str_repeat('>', $this->depth));
    }

    /**
     * @return void
     */
    private function increaseIndent()
    {
        $this->depth +=1;
    }

    /**
     * @return void
     */
    private function decreaseIndent()
    {
        if ($this->depth > 0) {
            $this->depth -=1;
        }
    }

    /**
     * @param string $line
     */
    private function printIndentedLine($line)
    {
        echo sprintf("%s%s\n", $this->indent(), $line);

        return null;
    }

    /**
     * @param Node\AbstractQueryItem $item
     */
    private function printPrefix(Node\AbstractQueryItem $item)
    {
        if ($item->isExcluded()) {
            return '-';
        }
        if ($item->isIncluded()) {
            return '+';
        }

        return null;
    }

    /**
     * @param Node\AbstractQueryItem $item
     */
    private function printPostfix(Node\AbstractQueryItem $item)
    {
        if ($item->isBoosted()) {
            return sprintf(' ^ %.2f', $item->getBoostBy());
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function visitWord(Node\Word $word)
    {
        return $this->printIndentedLine(sprintf(
            'Word (%s): %s%s%s',
            QueryLexer::$typeStrings[$word->getTokenType()],
            $this->printPrefix($word),
            $word->getToken(),
            $this->printPostfix($word)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function visitPhrase(Node\Phrase $phrase)
    {
        return $this->printIndentedLine(sprintf(
            'Phrase: %s%s%s',
            $this->printPrefix($phrase),
            $phrase->getToken(),
            $this->printPostfix($phrase)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function visitHashtag(Node\Hashtag $hashtag)
    {
        return $this->printIndentedLine(sprintf(
            'Hashtag: %s%s%s',
            $this->printPrefix($hashtag),
            $hashtag->getToken(),
            $this->printPostfix($hashtag)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function visitMention(Node\Mention $mention)
    {
        return $this->printIndentedLine(sprintf(
            'Mention: %s%s%s',
            $this->printPrefix($mention),
            $mention->getToken(),
            $this->printPostfix($mention)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function visitExplicitTerm(Node\ExplicitTerm $term)
    {
        if ($term->getNominator() instanceof Node\AbstractSimpleTerm) {
            return $this->printIndentedLine(sprintf(
                'Term: %s%s %s %s%s',
                $this->printPrefix($term),
                $term->getNominator()->getToken(),
                $term->getTokenTypeText(),
                $term->getTerm()->getToken(),
                $this->printPostfix($term)
            ));
        }

        $this->printIndentedLine(sprintf(
            'Term: %s%s %s%s',
            $this->printPrefix($term),
            $term->getTokenTypeText(),
            $term->getTerm()->getToken(),
            $this->printPostfix($term)
        ));
        $this->increaseIndent();

        $method = sprintf('visit%s', ucfirst(substr(get_class($term->getNominator()), 24)));
        if (method_exists($this, $method)) {
            $this->$method($term->getNominator());
        }

        $this->decreaseIndent();

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function visitSubExpression(Node\SubExpression $sub)
    {
        $this->printIndentedLine('Subexpression');
        $this->increaseIndent();
        $sub->getExpression()->accept($this);
        $this->decreaseIndent();

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function visitOrExpressionList(Node\OrExpressionList $list)
    {
        $this->printIndentedLine('Or');
        $this->increaseIndent();
        foreach ($list->getExpressions() as $expression) {
            $expression->accept($this);
        }
        $this->decreaseIndent();

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function visitAndExpressionList(Node\AndExpressionList $list)
    {
        $this->printIndentedLine('And');
        $this->increaseIndent();
        foreach ($list->getExpressions() as $expression) {
            $expression->accept($this);
        }
        $this->decreaseIndent();

        return null;
    }
}
