<?php

namespace Gdbots\QueryParser\Visitor;

use Gdbots\QueryParser\Node;

class QueryItemPrinter implements QueryItemVisitorinterface
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
    }

    /**
     * {@inheritDoc}
     */
    public function visitWord(Node\Word $word)
    {
        $this->printIndentedLine(sprintf('Word: %s', $word->getToken()));
    }

    /**
     * {@inheritDoc}
     */
    public function visitText(Node\Text $text)
    {
        $this->printIndentedLine(sprintf('Text: %s', $text->getToken()));
    }

    /**
     * {@inheritDoc}
     */
    public function visitExplicitTerm(Node\ExplicitTerm $term)
    {
        if ($term->getNominator() instanceof Node\SimpleTerm) {
            $this->printIndentedLine(sprintf('Term: %s %s %s', $term->getNominator()->getToken(), $term->getTokenTypeText(), $term->getTerm()->getToken()));

        } else {
            $this->printIndentedLine(sprintf('Term: %s %s', $term->getTokenTypeText(), $term->getTerm()->getToken()));
            $this->increaseIndent();

            if ($term->getNominator() instanceof Node\SubExpression) {
                $this->visitSubExpression($term->getNominator());
            } elseif ($term->getNominator() instanceof Node\ExcludeTerm) {
                $this->visitExcludeTerm($term->getNominator());
            } elseif ($term->getNominator() instanceof Node\IncludeTerm) {
                $this->visitIncludeTerm($term->getNominator());
            } elseif ($term->getNominator() instanceof Node\Hashtag) {
                $this->visitHashtag($term->getNominator());
            } elseif ($term->getNominator() instanceof Node\Mention) {
                $this->visitMention($term->getNominator());
            } elseif ($term->getNominator() instanceof Node\ExplicitTerm) {
                $this->visitExplicitTerm($term->getNominator());
            }

            $this->decreaseIndent();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function visitSubExpression(Node\SubExpression $sub)
    {
        $this->printIndentedLine('Subexpression');
        $this->increaseIndent();
        $sub->getSubExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitExcludeTerm(Node\ExcludeTerm $term)
    {
        $this->printIndentedLine('ExcludeTerm');
        $this->increaseIndent();
        $term->getSubExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitIncludeTerm(Node\IncludeTerm $term)
    {
        $this->printIndentedLine('IncludeTerm');
        $this->increaseIndent();
        $term->getSubExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitHashtag(Node\Hashtag $hashtag)
    {
        $this->printIndentedLine('Hashtag');
        $this->increaseIndent();
        $hashtag->getSubExpression()->accept($this);
        $this->decreaseIndent();
    }

    /**
     * {@inheritDoc}
     */
    public function visitMention(Node\Mention $mention)
    {
        $this->printIndentedLine('Mention');
        $this->increaseIndent();
        $mention->getSubExpression()->accept($this);
        $this->decreaseIndent();
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
    }
}
